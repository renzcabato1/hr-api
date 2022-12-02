<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IclockTransaction extends Model
{
    //
    protected $connection = 'sqlsrv_hris';
    protected $table = 'iclock_transaction';
}
