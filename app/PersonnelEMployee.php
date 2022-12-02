<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonnelEMployee extends Model
{
    //
    protected $connection = 'sqlsrv_hris';
    protected $table = 'personnel_employee';
}
