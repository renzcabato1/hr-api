<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeAttendance extends Model
{
    //

    protected $connection = 'sqlsrv_test';
    protected $table = 'employee_attendance';
}
