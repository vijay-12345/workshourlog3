<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\User, App\UserLeave, App\Task;
use Config;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;


class TaskController extends \App\Http\Controllers\Controller 
{

    public function edit($date_type,Request $request)
	{
		try {
				if($request->isMethod('post'))
				{
				    $header_token = $request->bearerToken();
					$model = User::where('token','=',$header_token)->first();
					if(!$model)
					{
						return $this->apiResponse(['message'=>'token is not valid'],true);
					}
					else if($model->role_id != 1)
					{
					    return $this->apiResponse(['message'=>'token is not admin and TL'],true);
					}

				    $day = $date_type;
				    $day_date = $day;
				    $data;
				    $user_leave;
				    $flag = false;
				    $userid = $request->user_id;
				    
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
				    	$data = Task::where('user_id','=',$userid)
				    	        ->where('deleted','0')
				    			->where(function($query) use ($day_date_next,$day_date_pre){
				    			$query->orWhere('date','>',$day_date_next)
				    			->orWhere('date','<=',$day_date_pre);
				    			})
				    			->orderBy('date','ASC')
				    			->get();
				    	$user_leave = UserLeave::where('user_id',$userid)->first();		
				    }
				    else
				    {
				        
				    	$day_date = Carbon::parse($day_date)->format('Y-m-d'); // for specific date eg. 2020-03-01
				    	$flag = true;
				    }
				    
				    if($flag == true)
				    {
                        $data = Task::where('user_id','=',$userid)
                                ->where('deleted','0')
				    			->where('date','=',$day_date)
				    			->orderBy('date','ASC')
				    			->get();
						$user_leave = UserLeave::where('user_id',$userid)->first();		
				        
				    }
				    return $this->apiResponse(['message'=>'successfully dashboard','data'=>$data,'user_leave'=>$user_leave]);
				}				
			} catch(\Exception $e) {
				return $this->apiResponse(['error'=>'Invalid data'],true);
			}		
	}
    
    public function deleteEmployee(Request $request)
    {
        try{
            $header_token = $request->bearerToken();
            $model = User::where('token','=',$header_token)->first();
            if( !$model ) return $this->apiResponse(['error'=>'Invalid token'],true);
            else if($model->role_id != 1)
                return $this->apiResponse(['error'=>'token is not for Admin and TL'],true);
            $user = User::where('id',$request->user_id)->first();
            $user->deleted = '1';
            $user->save();

            return $this->apiResponse(['message'=>'successfully employee soft delete']);

        }
        catch(Exception $e)
        {
            return $this->apiResponse(['error'=>$e->getMessage()],true);
        }
    }

    public function employeeList(Request $request)
    {
        try{
            $header_token = $request->bearerToken();
            $model = User::where('token','=',$header_token)->first();
            if( !$model ) return $this->apiResponse(['error'=>'Invalid token'],true);
            else if($model->role_id != 1)
                return $this->apiResponse(['error'=>'token is not Admin and TL'],true);
                
            $st_week = Carbon::now()->startOfWeek()->format('Y-m-d');
            $end_week = Carbon::now()->endOfWeek()->format('Y-m-d');
            //$tasks = Task::whereBetween('date',[$st_week,$end_week])->orderBy('user_id')->get();
            $tasks_pending = Task::where('completed','0')->where('deleted','0')->whereBetween('date',[$st_week,$end_week])->select('user_id',\DB::raw('COUNT(completed) as count'))->groupBy('user_id')->get();
            $tasks_total = Task::where('deleted','0')->whereBetween('date',[$st_week,$end_week])->select('user_id',\DB::raw('COUNT(completed) as count'))->groupBy('user_id')->get();
            $task_pending = [];
            $task_total = [];
            $count = 0;
            $userid;
            foreach($tasks_pending as $task)
            {
                $task_pending[$task->user_id] = $task->count; 
            }
            foreach($tasks_total as $task)
            {
                $task_total[$task->user_id] = $task->count; 
            }
            $users =User::all();
            $final = [];
            foreach ($users as $user) {
                //handle of employee only ,not admin and TL in this list
                if($user->role_id == 1)
                    continue;
                else if($user->deleted == 1)
                    continue;    
                $result =[];
                $result['user_id'] = $user->id;
                $result['name'] = $user->name;
                $result['mobile_number'] = $user->mobile_number;
                $result['email'] = $user->email;
                if(array_key_exists($user->id,$task_pending))
                {
                    $result['user_pending_task'] = $task_pending[$user->id];
                    $result['user_total_task'] = $task_total[$user->id];    
                }
                else
                {
                    $result['user_pending_task'] = 0;
                    $result['user_total_task'] = 0;
                }
                array_push($final,$result);   
            }
            
            //pagination
            $itemCollection = collect($final);
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $perPage = $request->limit;
            $offset = $request->skip;

            $array1 =[];
            if($offset == 0)
                $currentPageItems = $itemCollection->slice($offset, $perPage)->all();
            else
            {
                $count = 0;
                $end = $offset + $perPage;
                foreach ($final as $key => $val) {
                    if($count>=$offset && $count<$end )
                    {
                        array_push($array1,$val);
                    }
                    $count = $count + 1;
                }
                $currentPageItems = $array1;
            }
            //$currentPageItems = $itemCollection->slice($offset, $perPage)->all();
            $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
            return $this->apiResponse(['message'=>'employee list','data'=>$paginatedItems]);
        }
        catch(Exception $e)
        {
            return $this->apiResponse(['error'=>$e->getMessage()],true);
        }
    }




    public function getTask($type,Request $request)
    {
    	try{
            if($type == 'onLeave')
            {
                $st_week = Carbon::now()->startOfWeek()->format('Y-m-d');
                $end_week = Carbon::now()->endOfWeek()->format('Y-m-d');
                $today_date = Carbon::now()->format('Y-m-d');
                //$tasks = Task::select('user_id','completed')->where('completed','0')->whereBetween('date',[$st_week,$end_week])->orderBy('user_id')->groupBy('user_id')->get();
                $tasks = Task::where('completed','0')->where('deleted','0')->whereBetween('date',[$st_week,$end_week])->select('user_id',\DB::raw('COUNT(completed) as count'))->groupBy('user_id')->get();
                $task_pending = [];
                $userid;
                $count = 0;
                $final = [];
                foreach($tasks as $task)
                {
                    $task_pending[$task->user_id] = $task->count; 
                }
                $user_leaves =UserLeave::where('start_date',$today_date)->get();
                // print_r($user_leaves);
                // die;
                foreach($user_leaves as $user_leave)
                {
                    $user = User::where('id',$user_leave->user_id)->first();
                    if($user->role_id == 1)
                        continue;
                    if($user->deleted == 1)
                        continue;
                    $result = [];
                    $result['user_leave_id'] = $user_leave->id;
                    $result['user_leave_userid'] = $user_leave->user_id;
                    $result['user_name'] = $user->name;
                    $result['user_leave_leave_days'] = $user_leave->leave_days;
                    $result['user_leave_start_date'] = $user_leave->start_date;
                    $result['user_leave_end_date'] = $user_leave->end_date;
                    if(array_key_exists($user_leave->user_id,$task_pending))
                        $result['user_pending_task'] = $task_pending[$user_leave->user_id];
                    else
                        $result['user_pending_task'] = 0;
                    array_push($final,$result);
                }
                // print_r($final);
                // die;
                if($user_leaves)
                    return $this->apiResponse(['success'=>true,'message'=>'Dashboard of onLeave employee','data'=>$final]);
                else
                    return $this->apiResponse(['success'=>true,'message'=>'Not any employee is leave on today']);
            }
            else if($type == 'noTask')
            {
                $today_date = Carbon::now()->format('Y-m-d');
                $tasks = Task::select('user_id')->where('date',$today_date)->where('deleted','0')->groupBy('user_id')->orderBy('user_id')->get();
                $users = User::all();
                $user_ids = [];
                $i=0;
                foreach($tasks as $task)
                    array_push($user_ids,$task->user_id);
                foreach($users as $user)
                {
                    if($user->role_id == 1)
                        array_push($user_ids,$user->id);
                    else if($user->deleted == 1)
                        array_push($user_ids,$user->id);
                }
                    
                        
                $user = User::all()->except($user_ids);
                return $this->apiResponse(['message'=>'Dashboard of noTask employee','data'=>$user]);
            }

    	}
    	catch(Exception $e)
    	{
    		return $this->apiResponse(['error'=>$e->getMessage()],true);
    	}
    }


}
