<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\UserLeave ,App\User ,App\Task;
use Mail;
use Carbon\Carbon;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportController extends \App\Http\Controllers\Controller 
{
    public function report(Request $request)
    {
    	try{
    	        if($request->report_type == 'delayed')
                {
                    $final = [];
                    // $date = $request->date;
                    if(empty($request->date))
                    {
                        $start_date = Carbon::now()->format('Y-m-d');
                        $results = Task::where('date','<=',$start_date)->orderBy('user_id')->get();
                    }
                    else
                    {
                        $results = Task::where('date','<=',$request->date)->orderBy('user_id')->get();
                    }
                    $userids = [];
                    $userid_val;
                    foreach ($results as $result) {
                        if(empty($userids))
                        {

                            array_push($userids,$result->user_id);
                            $userid_val = $result->user_id;
                        }
                        else if($userid_val != $result->user_id)
                        {
                            array_push($userids,$result->user_id);
                            $userid_val = $result->user_id;
                        }
                    }
                    
                    foreach ($userids as $key => $userid) {
                        $datas = Task::where('user_id',$userid)->get();
                        $user = User::where('id',$userid)->first();
                        if($user->role_id == 1)
                            continue;
                        else if($user->deleted == 1)
                            continue;
                        $total_task = 0;
                        $completed = 0;
                        $total_estimated_time = 0;
                        $total_time_spent = 0;
                        foreach($datas as $data)
                        {
                            $total_task = $total_task +1;
                            if($data->completed == 1)
                            {
                                $completed = $completed +1;
                            }
                            if($data->estimated_time_value == "hour")
                            {
                                $total_estimated_time = $total_estimated_time + $data->estimated_time;
                            }
                            else
                            {
                                $time_in_hour = $data->estimated_time *(0.01666666666);
                                $total_estimated_time = $total_estimated_time + $time_in_hour;
                            }
                            if($data->time_spent_value == "hour")
                            {
                                $total_time_spent = $total_time_spent + $data->time_spent;
                            }
                            else
                            {
                                $time_in_hour = $data->time_spent *(0.01666666666);
                                $total_time_spent = $total_time_spent + $time_in_hour;
                            }
                        }
                        $result_array = [];
                        $result_array['user_name'] = $user->name;
                        $result_array['total_task'] = $total_task;
                        $result_array['completed'] = $completed;
                        $result_array['total_estimated_time'] = $total_estimated_time;
                        $result_array['total_time_spent'] = $total_time_spent;
                        $user_array =[];
                        $user_array['user'] = $result_array;
                        //array_push($user_array,$result_array);
                        array_push($final,$user_array);
                    }

                }
    	    
    			else if($request->report_type == 'onLeave')
    			{
                    $final = [];
                    // $date = $request->date;
                    if(empty($request->date))
                        $results = UserLeave::all();
                    else
                    {
                        // $results = UserLeave::where('start_date',$request->date)->get();
                        $results = UserLeave::where('start_date','<=',$request->date)->where('end_date','>=',$request->date)->get();
                    }
                    // print_r($results);
                    // die;
                    foreach($results as $result) {
                        $user_id = $result->user_id;
                        $user = User::where('id',$result->user_id)->first();
                        if($user->role_id == 1)
                            continue;
                        else if($user->deleted == 1)
                            continue;
                        $datas = Task::where('user_id',$user_id)->get();
                        $total_task = 0;
                        $completed = 0;
                        $total_estimated_time = 0;
                        $total_time_spent = 0;

                        foreach($datas as $data)
                        {
                            $total_task = $total_task +1;
                            if($data->completed == 1)
                            {
                                $completed = $completed +1;
                            }
                            if($data->estimated_time_value == "hour")
                            {
                                $total_estimated_time = $total_estimated_time + $data->estimated_time;
                            }
                            else
                            {
                                $time_in_hour = $data->estimated_time *(0.01666666666);
                                $total_estimated_time = $total_estimated_time + $time_in_hour;
                            }
                            if($data->time_spent_value == "hour")
                            {
                                $total_time_spent = $total_time_spent + $data->time_spent;
                            }
                            else
                            {
                                $time_in_hour = $data->time_spent *(0.01666666666);
                                $total_time_spent = $total_time_spent + $time_in_hour;
                            }
                        }
                        $result_array = [];
                        $result_array['user_name'] = $user->name;
                        $result_array['total_task'] = $total_task;
                        $result_array['completed'] = $completed;
                        $result_array['total_estimated_time'] = $total_estimated_time;
                        $result_array['total_time_spent'] = $total_time_spent;
                        $user_array =[];
                        $user_array['user'] = $result_array;
                        //array_push($user_array,$result_array);
                        array_push($final,$user_array);
                    }

    			}
    			else if($request->report_type == 'noTask')
    			{
                    $final = [];
                    if(empty($request->date))
                        $tasks = Task::orderBy('user_id')->get();
                    else
                        $tasks = Task::where('date','<=',$request->date)->where('date','>=',$request->date)->orderBy('user_id')->get();
                    $user_ids = [];
                    $i=0;
                    foreach ($tasks as $task) {
                        if(empty($user_ids))
                            array_push($user_ids,$task->user_id);
                        else
                        {
                            $val = $user_ids[$i];
                            if($task->user_id != $val)
                            {
                                array_push($user_ids,$task->user_id);
                                $i=$i+1;
                            }
                        }
                    }
                    $users = User::all()->except($user_ids);
                    foreach($users as $user) {
                        if($user->role_id == 1)
                            continue;
                        else if($user->deleted == 1)
                            continue;
                        $result_array = [];
                        $result_array['user_name'] = $user->name;
                        $result_array['total_task'] = 0;
                        $result_array['completed'] = 0;
                        $result_array['total_estimated_time'] = 0;
                        $result_array['total_time_spent'] = 0;
                        $user_array =[];
                        $user_array['user'] = $result_array;
                        array_push($final,$user_array);
                    }

    			}
                else
                {
                    $final = [];
                    if(empty($request->date))
                    {
                        // echo "good ";
                        // die;
                        $tasks = Task::orderBy('user_id')->get();
                        $results = UserLeave::all();
                    }
                    else
                    {
                        $tasks = Task::where('date','<=',$request->date)->where('date','>=',$request->date)->orderBy('user_id')->get();
                        $results = UserLeave::where('start_date','<=',$request->date)->where('end_date','>=',$request->date)->get();
                        
                        // print_r($results);
                        // die;
                    }

                    $user_ids = [];
                    $i=0;
                    foreach ($tasks as $task) {
                        if(empty($user_ids))
                            array_push($user_ids,$task->user_id);
                        else
                        {
                            $val = $user_ids[$i];
                            if($task->user_id != $val)
                            {
                                array_push($user_ids,$task->user_id);
                                $i=$i+1;
                            }
                        }
                    }
                    $users = User::all()->except($user_ids);
                    foreach($users as $user) {
                        if($user->role_id == 1)
                            continue;
                        else if($user->deleted == 1)
                            continue;
                        $result_array = [];
                        $result_array['user_name'] = $user->name;
                        $result_array['total_task'] = 0;
                        $result_array['completed'] = 0;
                        $result_array['total_estimated_time'] = 0;
                        $result_array['total_time_spent'] = 0;
                        $result_array['type_name'] = "noTask";        //for indentify data is for onLeave or noTask
                        $user_array =[];
                        $user_array['user'] = $result_array;
                        array_push($final,$user_array);
                    }
                    foreach($results as $result) {
                        $user_id = $result->user_id;
                        $datas = Task::where('user_id',$user_id)->get();
                        $user = User::where('id',$user_id)->first();
                        if($user->role_id == 1)
                            continue;
                        else if($user->deleted == 1)
                            continue;
                        $total_task = 0;
                        $completed = 0;
                        $total_estimated_time = 0;
                        $total_time_spent = 0;

                        foreach($datas as $data)
                        {
                            $total_task = $total_task +1;
                            if($data->completed == 1)
                            {
                                $completed = $completed +1;
                            }
                            if($data->estimated_time_value == "hour")
                            {
                                $total_estimated_time = $total_estimated_time + $data->estimated_time;
                            }
                            else
                            {
                                $time_in_hour = $data->estimated_time *(0.01666666666);
                                $total_estimated_time = $total_estimated_time + $time_in_hour;
                            }
                            if($data->time_spent_value == "hour")
                            {
                                $total_time_spent = $total_time_spent + $data->time_spent;
                            }
                            else
                            {
                                $time_in_hour = $data->time_spent *(0.01666666666);
                                $total_time_spent = $total_time_spent + $time_in_hour;
                            }
                        }
                        $result_array = [];
                        $result_array['user_name'] = $user->name;
                        $result_array['total_task'] = $total_task;
                        $result_array['completed'] = $completed;
                        $result_array['total_estimated_time'] = $total_estimated_time;
                        $result_array['total_time_spent'] = $total_time_spent;
                        $result_array['type_name'] = "onLeave";    //for indentify data is for onLeave or noTask
                        $user_array =[];
                        $user_array['user'] = $result_array;
                        array_push($final,$user_array);
                    }
                }
                
                
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
                
                if($request->report_type == 'delayed')
                {
                    return $this->apiResponse(['message'=>'report of delayed', 'data' =>$paginatedItems]);
                }
                else if($request->report_type == 'onLeave')
                {
                    return $this->apiResponse(['message'=>'report of On leave', 'data' =>$paginatedItems]);
                }
                else if($request->report_type == 'noTask')
                {
                    return $this->apiResponse(['message'=>'report of no Task', 'data' =>$paginatedItems]);
                }
                else
                {
                    return $this->apiResponse(['message'=>'report of All', 'data' =>$paginatedItems]);
                }
    	}
    	catch(Exception $e)
    	{
    		return $this->apiResponse(['error'=>$e->getMessage()],true);
    	}
    }
}
