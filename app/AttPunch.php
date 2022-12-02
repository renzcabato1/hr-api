<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AttPunch extends Model
{
    //
    public function personal_data()
    {
        return $this->hasOne(HrEmployee::class,'id','employee_id');
    }
}
