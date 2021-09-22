<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::get('fun', 'NewController@fun');
Route::post('login', 'LoginController@login');
Route::post('register', 'LoginController@register');
Route::post('update', 'LoginController@update');
Route::get('update', 'LoginController@update');
Route::post('email-verify','LoginController@emailVerified');
Route::post('forgot-password','LoginController@forgotPasswordFunction');
Route::post('reset-password', 'LoginController@resetPassword');
Route::post('add-new-task', 'TaskController@add');
Route::post('edit-task/{date_type}', 'TaskController@edit');
Route::get('edit-task/{date_type}', 'TaskController@edit');
Route::get('get-task/{date_type}', 'TaskController@getTask');
Route::post('get-task/{date_type}', 'TaskController@getTask');
Route::get('resume-pdf-path','LoginController@resumePdfPath');
Route::get('project-list','ProjectController@projectList');  //today
Route::get('add-task-dropdown-projects','TaskController@dropDownProject');
//Route::get('report/{report_type}','ReportController@report');


Route::get('admin/get-task/{type}','Admin\TaskController@getTask');
// Route::post('admin/add-new-task','Admin\TaskController@add');
Route::post('admin/employee-list','Admin\TaskController@employeeList');
Route::post('admin/delete-employee','Admin\TaskController@deleteEmployee');
Route::post('edit-task-byId/{date_type}', 'Admin\TaskController@edit');
Route::post('admin/report','Admin\ReportController@report');
Route::post('admin/profile-update/{user_id}','Admin\LoginController@update');
Route::get('admin/profile-update/{user_id}','Admin\LoginController@update');
Route::post('admin/add-project/{team_id}','Admin\ProjectController@addProject');    //today
Route::get('admin/add-project/{team_id}','Admin\ProjectController@addProject');
Route::post('admin/delete-project','Admin\ProjectController@deleteProject');
Route::post('admin/resume-pdf-path','Admin\LoginController@resumePdfPath');
Route::post('admin/project-management/{team_id}/{project_id}','Admin\ProjectController@projectManagement');
Route::get('admin/project-management/{team_id}/{project_id}','Admin\ProjectController@projectManagement');
Route::post('admin/delete-project-management','Admin\ProjectController@deleteProjectManagement');				
Route::post('admin/report-project-management','Admin\ProjectController@reportProjectManagement');
Route::get('admin/report-dropdown-team','Admin\ProjectController@reportDropDownTeam');



//team managment routes
Route::post('team-add','Admin\TeamController@addTeam');
Route::post('team-edit','Admin\TeamController@editTeam');
Route::get('team-add','Admin\TeamController@addTeam');
Route::post('team-member-add','Admin\TeamController@addTeamMember');
Route::post('team-member-show','Admin\TeamController@showTeamMember');
Route::post('delete-team-member','Admin\TeamController@deleteTeamMember');
Route::post('delete-team','Admin\TeamController@deleteTeam');
Route::post('regsiter1','Admin\TeamController@register');




