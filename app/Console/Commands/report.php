<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\UserLeave, App\User, App\Task;
use Mail;
use Config; 


class report extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:employee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a Daily email to TL and Admin with onLeave and Task update';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {   
        $this->info('Cron Job Started');
        //$date = Carbon::now()->addDays(1)->format('Y-m-d');
        //$results = UserLeave::where('start_date',$date)->get();
        $user_array = [];
        $result_array = [];
        $date = Carbon::now()->format('Y-m-d');
        $results = Task::where('date','<=',$date)->orderBy('user_id')->get();
        $data2 = [];
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
            $total_task = 0;
            $delayed_task = 0;
            foreach($datas as $data)
            {
                if($data->completed == 0)
                    $delayed_task = $delayed_task + 1;
                $total_task = $total_task + 1;
            }
            $result_array = [];
            $result_array['user_name'] = $user->name;
            $result_array['total_task'] = $total_task;
            $result_array['delayed_task'] = $delayed_task;
            //$user_array =[];
            //$user_array['user'] = $result_array;
            //array_push($user_array,$result_array);
            array_push($data2,$result_array);
        }
        $data3 = [];
        $data3['users'] = $data2;
        $data3['email'] = Config::get('constants.email');

        foreach ($data3['email'] as $x =>$config_email) {
            // print_r($data3['email'][$x]);
            Mail::send('emails.delayed', $data3, function($message) use ($data3,$x) {
                    $message->to($data3['email'][$x]);
                    //$message->to('vijay.anand@enukesoftware.com');
                    $message->subject('Delayed task e-mail of employee report');
                });    
        }
                
        
        //report of onLeave
        $date = Carbon::now()->addDays(1)->format('Y-m-d');
        $results = UserLeave::where('start_date','<=',$date)->where('end_date','>=',$date)->get();
        $data2 =[];
        foreach($results as $result) {
            $user_id = $result->user_id;
            $datas = Task::where('user_id',$user_id)->get();
            $user = User::where('id',$user_id)->first();
            if($user->role_id == 1)
                continue;
            // print_r($user);
            // die;
            $total_task = 0;
            $completed = 0;
            $total_estimated_time = 0;
            $total_time_spent = 0;
            //$data2 =[];

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
            $result_array['name'] = $user->name;
            $result_array['total_task'] = $total_task;
            $result_array['completed'] = $completed;
            $result_array['total_estimated_time'] = round($total_estimated_time);
            $result_array['total_time_spent'] = round($total_time_spent);
            $result_array['untill_leave'] = $result->end_date;
            array_push($data2,$result_array);

        }
        $data3 = [];
        $data3['users'] = $data2;
        $data3['email'] = Config::get('constants.email');

        foreach ($data3['email'] as $x =>$config_email) {
            // print_r($data3['email'][$x]);
            Mail::send('emails.report', $data3, function($message) use ($data3,$x) {
                    $message->to($data3['email'][$x]);
                    //$message->to('vijay.anand@enukesoftware.com');
                    $message->subject('onLeave e-mail of employee report');
                });    
        }


        //report of noTask 

        $tasks =Task::where('date',$date)->orderBy('user_id')->get();
        $user_ids = [];
        $pre;
        foreach ($tasks as $task) {
            if(empty($user_ids))
            {
                array_push($user_ids,$task->user_id);
                $pre = $task->user_id;
            }
            else
            {
                if($task->user_id != $pre)
                {
                    array_push($user_ids,$task->user_id);
                    $pre = $task->user_id;
                }

            }
        }

        $users = User::all()->except($user_ids);
        $data2 = [];
        foreach ($users as $user) {
            if($user->role_id == 1)
                continue;
            $datas = Task::where('user_id',$user->id)->get();
            $total_task = 0;
            $completed = 0;
            $total_estimated_time = 0;
            $total_time_spent = 0;
            //$data2 =[];

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
            $result_array['name'] = $user->name;
            $result_array['total_task'] = $total_task;
            $result_array['completed'] = $completed;
            $result_array['total_estimated_time'] = round($total_estimated_time);
            $result_array['total_time_spent'] = round($total_time_spent);
            //$result_array['untill_leave'] = $result->end_date;
            array_push($data2,$result_array);
        }
        $data3['users'] = $data2;
        $data3['email'] = Config::get('constants.email');

        foreach ($data3['email'] as $x =>$config_email) {
            // print_r($data3['email'][$x]);
            Mail::send('emails.notask', $data3, function($message) use ($data3,$x) {
                    $message->to($data3['email'][$x]);
                    //$message->to('vijay.anand@enukesoftware.com');
                    $message->subject('noTask e-mail of employee report');
                });    
        }
         
        $this->info('Report of employee sent to All TL and admin');
    }
}
