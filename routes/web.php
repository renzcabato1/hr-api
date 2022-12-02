<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('get-attendance','AttendanceController@get_attendance');
Route::get('get-bio','AttendanceController@get_attendance_bio');
Route::get('get-employees','AttendanceController@getEmployees');