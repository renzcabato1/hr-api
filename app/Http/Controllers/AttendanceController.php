<?php

namespace App\Http\Controllers;
use App\AttPunch;
use App\IclockTransaction;
use App\HrEmployee;
use App\Attendance;
use App\PersonnelEMployee;
use App\EmployeeAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
class AttendanceController extends Controller
{
    //
    public function get_attendance()
    {

        
        $attendances = Attendance::where('time_out',null)->get();
        foreach($attendances as $att)
        {
            $time_in_after = date('Y-m-d H:i:s',strtotime($att->time_in));
            $time_in_before = date('Y-m-d H:i:s', strtotime ( '+18 hour' , strtotime ( $time_in_after ) )) ;
            $time_out_data = Attendance::whereBetween('time_out',[ $time_in_after,$time_in_before])->orderBy('time_out','desc')->first();
            if($time_out_data != null)
            {
                $att->time_out = date('Y-m-d H:i:s', strtotime($time_out_data->time_out));
                $att->last_id = $time_out_data->last_id;
                $att->device_out = "Administrator Manual Generate";
                $att->save();
            }
            
        }

    }
    function old()
    {
        $last_attendance = Attendance::orderBy('last_id','desc')->first();

        if($last_attendance == null)
        {
            $last_id = 0;
        }
        else
        {
            $last_id = $last_attendance->last_id;
        }
       
        $attendances = IclockTransaction::where('id','>',$last_id)->orderBy('id','asc')->get();
        foreach($attendances as $att)
            {
              if($att->punch_state == 0)
                {
                   
                        $attendance = new Attendance;
                        $attendance->employee_code  = $att->emp_code;   
                        $attendance->time_in = date('Y-m-d H:i:s', strtotime ( '+0 hour' ,strtotime($att->punch_time)));
                        $attendance->device_in = $att->terminal_alias;
                        $attendance->last_id = $att->id;
                        $attendance->save(); 
                    
                }
                else if($att->punch_state == 1)
                {
                    $time_in_after = date('Y-m-d H:i:s',strtotime($att->punch_time));
                    $time_in_before = date('Y-m-d H:i:s', strtotime ( '-24 hour' , strtotime ( $time_in_after ) )) ;
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
    }
    public function getEmployees()
    {
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
        
        return "Success";
    }




    public function get_attendance_bio()
    {
        DB::connection("sqlsrv_dev")->statement('exec LoadEmployeeDTRDetails');
        DB::connection("sqlsrv_test")->statement('exec LoadEmployeeDTROBNDetails');

        return "success";
    }
}
