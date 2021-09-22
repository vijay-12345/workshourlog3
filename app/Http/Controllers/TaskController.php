<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Validator;
use App\User;
use App\Task;
use App\UserLeave;
use \DB;
use Auth;
use Response;
use Carbon\Carbon;

class TaskController extends Controller
{
	public function dropDownProject(Request $request)
	{
		try{
				$header_token = $request->bearerToken();
				$user = User::where('token','=',$header_token)->first();
				if( !$user ) 
					return $this->apiResponse(['error'=>'Invalid token'],true);

				$team_details = TeamDetail::select('team_id')->where('user_id',$user->id)->get();
				$final_array = [];
				foreach ($team_details as $team_detail) {
					$projects = Project::select('id','name')->where('team_id',$team_detail->team_id)->get();
					foreach ($projects as $project) {
						array_push($final_array,$project);
					}
				}
				return $this->apiResponse(['message'=>'drop down list of projects','data'=>$final_array]);

		}
		catch(Exception $e)
		{
			return $this->apiResponse(['error'=>'Invalid data'],true);
		}
	}
	
	public function edit($date_type,Request $request)
	{
		try {
		        $header_token = $request->bearerToken();
				$data = User::where('token','=',$header_token)->first();
				if(!$data)
					return $this->apiResponse(['message'=>'token is not valid'],true);
				
				if($request->isMethod('post'))
				{
                    $userid;
					if($data->role_id == 1)
					    $userid = $request->user_id;
					else
					    $userid = $data['id'];
					if($request->coming_tomorrow['status'] == false)
					{
						$end_date = Carbon::parse($request->coming_tomorrow['till_date']);
						$start_date = Carbon::now()->format('Y-m-d');
						$leave_days = $end_date->diffInDays($start_date);
						$user_leave = UserLeave::where('user_id',$userid)->first();
						if($user_leave)
						{
							$user_leave->leave_days = $leave_days;
							$user_leave->start_date = $start_date;
							$user_leave->end_date = $end_date;
							$user_leave->save();
						}
						else{
						UserLeave::create(['user_id'=>$userid,
											'leave_days'=>$leave_days,
											'start_date'=>$start_date,
											'end_date'=>$end_date
					 					]);
						}
					}
					else
    				{
    				    $data = UserLeave::where('user_id',$userid)->first();
    				    if($data)
    				        $data->delete();
    				}
					$data_size = sizeof($request->new_task['data']);
					$i =0;
					if($data_size>=1)
					{
						while($i<$data_size)
						{
							$task = Task::where('id','=',$request->new_task['data'][$i]['id'])->first();
							$task->task_name = $request->new_task['data'][$i]['task_name'];
							if($request->new_task['for_tomorrow'] == true)
							{
								$task->date = $request->new_task['completion_date'];
							}
							if($request->new_task['data'][$i]['deleted'] != null)
							{
								//return $this->apiResponse(['error'=>'task not edited for particular '],true);
								 $task->deleted = $request->new_task['data'][$i]['deleted'];
							}
							$task->completed = $request->new_task['data'][$i]['completed'];
							$task->estimated_time = $request->new_task['data'][$i]['estimated_time'];
							$task->time_spent = $request->new_task['data'][$i]['time_spent'];
							$task->time_spent_value = $request->new_task['data'][$i]['time_spent_value'];
							$task->estimated_time_value = $request->new_task['data'][$i]['estimated_time_value'];
							$task->save();
							$i = $i + 1;
						}
						return $this->apiResponse(['message'=>'successfully task edited']);
					}
					else{
							return $this->apiResponse(['error'=>'task not edited'],true);
						}	
				}
				else if($request->isMethod('get'))
				{
				    $day = $date_type;
				    $day_date = $day;
				    $task;
				    $user_leave;
				    $flag = false;
				    if($day_date == 'today')
				    {
				        $day_date = Carbon::now()->format('Y-m-d');
				        $flag = true;
				    }
				    else if($day_date == 'tomorrow')
				    {
				        $day_date = Carbon::now()->addDays(1)->format('Y-m-d');
				        $flag = true;
				    }
				    else if($day_date == 'upcoming')
				    {
				        $day_date_next = Carbon::now()->addDays(1)->format('Y-m-d'); //find date is equal to tomorrow
				    	$day_date_pre = Carbon::now()->subDays(1)->format('Y-m-d'); //find date is equal to yesterday
				    	$task = Task::where('user_id','=',$data['id'])
				    			->where(function($query) use ($day_date_next,$day_date_pre){
				    			$query->orWhere('date','>',$day_date_next)
				    			->orWhere('date','<=',$day_date_pre);
				    			})
				    			->orderBy('date','ASC')
				    			->get();
				    	$user_leave = UserLeave::where('user_id',$data['id'])->first();	
				    	
				    }
				    else
				    {
				        $day_date = Carbon::parse($day_date)->format('Y-m-d');
				        $flag = true;
				    }
				    if($flag == true){
        			    $task = Task::where('user_id',$data['id'])
        			    			->where('date','=',$day_date)
        			    			->orderBy('date','ASC')
        			    			->get();
        			    $user_leave = UserLeave::where('user_id',$data['id'])->first();
				    }
				    return $this->apiResponse(['message'=>'successfully dashboard','data'=>$task,'user_leave'=>$user_leave]);
				}				
			} catch(\Exception $e) {
				return $this->apiResponse(['error'=>'Invalid data'],true);
			}		
	}

	public function getTask($date_type, Request $request)
	{
		try {
		    $header_token = $request->bearerToken();
			$model = User::where('token','=',$header_token)->first();
		    if( !$model ) return $this->apiResponse(['error'=>'Invalid token'],true);

		    $user_id;
		    if($request->isMethod('post'))
            {
			    if($model->role_id == 1)
			    	$user_id = $request->user_id;
			    else
			    	$user_id = $model->id;
            }
            else if($request->isMethod('get'))
            {
                $user_id = $model->id;
            }

			    $day = $date_type;
				$day_date = $day;
				$data;
				$flag = false;
			    if($day_date == 'today')
			    {
			        $day_date = Carbon::now()->format('Y-m-d');
				    $flag = true;
			    }
			    else if($day_date == 'tomarrow')
			    {
			        $day_date = Carbon::now()->addDays(1)->format('Y-m-d');
				    $flag = true;
			    }
			    else if($day_date == 'upcoming')
			    {
			    	$day_date_next = Carbon::now()->addDays(1)->format('Y-m-d');
			    	$day_date_pre = Carbon::now()->subDays(1)->format('Y-m-d');
			    	$data = Task::where('user_id','=',$user_id)
			    	        ->where('deleted','0')
			    			->where(function($query) use ($day_date_next,$day_date_pre){
			    			$query->orWhere('date','>',$day_date_next)
			    			->orWhere('date','<=',$day_date_pre);
			    			})
			    			->orderBy('date','ASC')
			    			->get();
			    	$flag = false;
			    }
			    else
			    {
			        $day_date = Carbon::parse($day_date)->format('Y-m-d');
				    $flag = true;
			    }	
			    if($flag == true){
    			    $data = Task::where('user_id','=',$user_id)
    			            ->where('deleted','0')
			    			->where('date','=',$day_date)
			    			->orderBy('date','ASC')
			    			->get();
			    }
			    
			    return $this->apiResponse(['message'=>'successfully dashboard','data'=>$data]);
			}
			catch(\Exception $e) {
				return $this->apiResponse(['error'=>'Invalid token'],true);
			}
	}


	public function add(Request $request)
	{
		try {
				$userid;
				$header_token = $request->bearerToken();
				$user = User::where('token','=',$header_token)->first();
				if(!$user)
					return $this->apiResponse(['message'=>'token is not valid'],true);
				else if($user->role_id == 1)
					$userid = $request->user_id;
				else
					$userid = $user->id;	// other than admin and TL case
				if($request->coming_tomorrow['status'] == false)
				{
					$end_date = Carbon::parse($request->coming_tomorrow['till_date']);
					
					$start_date = Carbon::now()->format('Y-m-d');
					$leave_days = $end_date->diffInDays($start_date);
					$user_leave = UserLeave::where('user_id',$userid)->first();
					if($user_leave)
					{
						$user_leave->leave_days = $leave_days;
						$user_leave->start_date = $start_date;
						$user_leave->end_date = $end_date;
						$user_leave->save();
				
					}
					else
					{
					UserLeave::create(['user_id'=>$userid,
										'leave_days'=>$leave_days,
										'start_date'=>$start_date,
										'end_date'=>$end_date
				 					]);
					}
				}
				else
				{
				    $data = UserLeave::where('user_id',$userid)->first();
				    if($data)
				        $data->delete();
				}
				if($request->new_task['for_tomorrow'] == true)
				{
					$data_size = sizeof($request->new_task['data']);
					$i =0;
					if($data_size>=1)
					while($i<$data_size)
					{
						if($request->new_task['data'][$i]['isCompleted'] == false)
						{
							$completed = 0;
						}
						else
						{
							$completed = 1;						
						}
						Task::create(['task_name'=>$request->new_task['data'][$i]['task_name'],								
								    'user_id'=>$userid,
									'date'=>$request->new_task['completion_date'],
									'completed'=>$completed,
									'time_spent'=>$request->new_task['data'][$i]['time_spent'],
									'time_spent_value'=>$request->new_task['data'][$i]['time_spent_value'],
									'estimated_time'=>$request->new_task['data'][$i]['est_time'],
									'estimated_time_value'=>$request->new_task['data'][$i]['est_time_value']
									   ]);
						//   echo "good morning";
						// die;
						  $i = $i + 1;
					}
					return $this->apiResponse(['message'=>'successfully task added']);
				}
				else{
						return $this->apiResponse(['message'=>'task not add']);
					}	
			} catch(\Exception $e) {
				return $this->apiResponse(['error'=>$e->getMessage()],true);
			}
	}


}
