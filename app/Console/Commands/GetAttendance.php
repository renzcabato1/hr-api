<?php

namespace App\Console\Commands;
use App\AttPunch;
use App\IclockTransaction;
use App\HrEmployee;
use App\Attendance;
use App\PersonnelEMployee;
use App\EmployeeAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class GetAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getAttendance:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    //    dd('renz');
    // asdasd
        // DB::connection("sqlsrv_dev")->statement('exec LoadEmployeeDTRDetails');
        DB::connection("sqlsrv_test")->statement('exec LoadEmployeeDTROBNDetails');

        DB::connection("sqlsrv_plc")->statement('exec LoadEmployeeDTROBNDetails');
        DB::connection("sqlsrv_plc")->statement('exec LoadEmployeeDTROBNDetails');

        DB::connection("sqlsrv_pchi")->statement('exec LoadEmployeeDTROBNDetails');
        DB::connection("sqlsrv_pchi")->statement('exec LoadEmployeeDTROBNDetails');

        $last_attendance = Attendance::orderBy('last_id','desc')->first();

        if($last_attendance == null)
        {
            $last_id = 0;
        }
        else
        {
            $last_id = $last_attendance->last_id;
        }
        // dd($last_id);
        $attendances_manuals = EmployeeAttendance::where('emp_id',null)->orWhere('emp_id','0')->get();
        
        foreach($attendances_manuals as $att)
        {
            // dd($att);
            $asd = EmployeeAttendance::where(function($q) {
                $q->where('emp_id',"!=", null)
                  ->Where('emp_id',"!=", 0);
            })
            ->where('punch_date',$att->punch_date)
            ->where('emp_code',$att->emp_code)->delete();
        }
        $attendances = IclockTransaction::where('id','>',$last_id)->orderBy('id','asc')->get()->take(200);
        foreach($attendances as $att)
            {
              if($att->punch_state == 0)
                {
                        $attend = Attendance::where('employee_code',$att->emp_code)->whereDate('time_in',date('Y-m-d', strtotime($att->punch_time)))->first();
                        if($attend == null)
                        {
                            $attendance = new Attendance;
                            $attendance->employee_code  = $att->emp_code;   
                            $attendance->time_in = date('Y-m-d H:i:s',strtotime($att->punch_time));
                            $attendance->device_in = $att->terminal_alias;
                            $attendance->last_id = $att->id;
                            $attendance->save(); 
                        }
                    
                }
                else if($att->punch_state == 1)
                {
                    $time_in_after = date('Y-m-d H:i:s',strtotime($att->punch_time));
                    $time_in_before = date('Y-m-d H:i:s', strtotime ( '-18 hour' , strtotime ( $time_in_after ) )) ;
                    $update = [
                        'time_out' =>  date('Y-m-d H:i:s', strtotime($att->punch_time)),
                        'device_out' => $att->terminal_alias,
                        'last_id' =>$att->id,
                    ];

                    $attendance_in = Attendance::where('employee_code',$att->emp_code)
                    ->whereBetween('time_in',[$time_in_before,$time_in_after])->first();
                    Attendance::where('employee_code',$att->emp_code)
                    ->whereBetween('time_in',[$time_in_before,$time_in_after])
                    ->update($update);

                    if($attendance_in ==  null)
                    {
                        $attendance = new Attendance;
                        $attendance->employee_code  = $att->emp_code;   
                        $attendance->time_out = date('Y-m-d H:i:s', strtotime($att->punch_time));
                        $attendance->device_out = $att->terminal_alias;
                        $attendance->last_id = $att->id;
                        $attendance->save(); 
                    }
                }
            }
            $data_attendances = IclockTransaction::where('id_last','!=',null)->orderBy('id_last','desc')->first();
            //    dd($data_attendances->id_last);
                $attendances = AttPunch::with('personal_data')->where('id','>',$data_attendances->id_last)->orderBy('id','asc')->get();
            //    dd($attendances[0]);
               foreach($attendances as $att)
               {
        
                $id = 0;
                $emp = PersonnelEMployee::where('emp_code',$att->personal_data->emp_pin)->first();
                if($emp != null)
                {
                    $id = $emp->id;
                }
                else
                {
                        $new_emp = new PersonnelEMployee;
                        $new_emp->status = 0;
                        $new_emp->first_name = $att->personal_data->emp_firstname;
                        $new_emp->last_name = $att->personal_data->emp_lastname;
                        $new_emp->emp_code = $att->personal_data->emp_pin;
                        
                        $new_emp->is_admin = 0;
                        $new_emp->deleted = 0;
                        $new_emp->is_active = 1;
                        $new_emp->enable_payroll = 1;
                        $new_emp->save();
        
                        $id = $new_emp->id;
                }
                $data = IclockTransaction::where('emp_code',$att->personal_data->emp_pin)->where('punch_time',$att->punch_time)->first();
                if($data == null)
                    {
                            
                            $new_iclock = new IclockTransaction;
                            $new_iclock->emp_code = $att->personal_data->emp_pin;
                            $new_iclock->punch_time = $att->punch_time;
                            $new_iclock->punch_state = $att->workstate;
                            $new_iclock->verify_type = $att->verifycode;
                            $new_iclock->terminal_alias = "17th Floor";
                            $new_iclock->is_attendance = 1;
                            $new_iclock->is_mask = 255;
                            $new_iclock->emp_id = $id;
                            $new_iclock->temperature = 255.0;
                            $new_iclock->terminal_id_def = $att->terminal_id;
                            $new_iclock->id_last = $att->id;
                            $new_iclock->save();
                    }
               }

        $employees = PersonnelEMployee::get();
        foreach($employees as $employee)
        {
            $check_employee = HrEmployee::where('emp_pin',$employee->emp_code)->first();
            if($check_employee == null)
            {
               $newEmployee = new HrEmployee;
               $newEmployee->emp_pin = $employee->emp_code;
               $newEmployee->emp_firstname = $employee->first_name;
               $newEmployee->emp_lastname = $employee->last_name;
               $newEmployee->emp_lastname = $employee->last_name;
               $newEmployee->emp_privilege = 0;
               $newEmployee->emp_active = 1;
               $newEmployee->emp_cardNumber = 0;
               $newEmployee->emp_hourlyrate1 = .00000;
               $newEmployee->emp_hourlyrate2 = .00000;
               $newEmployee->emp_hourlyrate3 = .00000;
               $newEmployee->emp_hourlyrate4 = .00000;
               $newEmployee->emp_hourlyrate5 = .00000;
               $newEmployee->emp_gender = 0;
               $newEmployee->emp_operationmode = 0;
               $newEmployee->IsSelect = 0;
               $newEmployee->middleware_id = 0;
               $newEmployee->department_id = 1;
               $newEmployee->position_id = 1;
               $newEmployee->save();
            }
        }
        
        \Log::info("Cron is working fine! with exec : Done");
    }
}
