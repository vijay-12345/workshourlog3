<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Team;
use App\User;
use App\TeamDetail;
use \Validator;
use \DB;


class TeamController extends \App\Http\Controllers\Controller
{
//     public function register(Request $request)
//     {
//         try{
//             $validator = Validator::make(
// 		        $request->all(),[
// 		            'email' => 'required',
// 		            'password' => 'required',
// 		            'name' => 'required',
// 		            'mobile_number'=>'required'
// 		        ]
// 		    );
// 			if($validator->fails()) $this->apiResponse(['error'=>'validation is not fullfill'],true);
			
//             $user1 = User::create(['email'=>$request->email,
//             						'password' => bcrypt($request->password),
//             						'name' => $request->name, 
//             						'mobile_number' =>$request->mobile_number
//         						]);
            
//             $id = \DB::getPdo()->lastInsertId();
// 			$user = User::findOrFail($id);

//             if($user)
//             {
//             	$token = md5(uniqid($request->email, true));
//             	$user->token = $token;
//             	$user->role_id = '2';
//             	if ($request->file('resume')) {
//                   $destinationPath = 'public/resume/'; // upload path
//                   $profileImage = date('YmdHis') . "." . $request->file('resume')->getClientOriginalExtension();
//                   $request->file('resume')->move($destinationPath, $profileImage);
//                   //$insert['image'] = "$profileImage";
//                   $user->resume = $profileImage;
//                 }
//             	$user->save();
//         		return $this->apiResponse(['message' => 'Successfully send the link on email for verify Register', 'data' => $user]);
//             }
//         	else
//         	{
//         		return $this->apiResponse(['error'=>'data not Register'],true);
//         	}
//         }
//         catch(Exception $e)
//         {
//             return $this->apiResponse(['error'=>$e->getMessage()],true);
//         }
//     }
    
    public function deleteTeamMember(Request $request)
    {
        try{
            $header_token = $request->bearerToken();
			$model = User::where('token','=',$header_token)->first();
			if( !$model ) 
				return $this->apiResponse(['error'=>'Invalid token'],true);
			else if($model->role_id != 1)
				return $this->apiResponse(['error'=>'Token is not for Admin and TL'],true);
			$team_detail = TeamDetail::find($request->team_member_id);
			
            $team_detail->delete();
            return $this->apiResponse(['message'=>'Successfully deleted team member']);
            
        }
        catch(Exception $e)
        {
            return $this->apiResponse(['error'=>$e->getMessage()],true);
        }
    }
    
    public function deleteTeam(Request $request)
    {
        try{
            $header_token = $request->bearerToken();
			$model = User::where('token','=',$header_token)->first();
			if( !$model ) 
				return $this->apiResponse(['error'=>'Invalid token'],true);
			else if($model->role_id != 1)
				return $this->apiResponse(['error'=>'Token is not for Admin and TL'],true);
			$team = Team::find($request->team_id);
			$team_details = TeamDetail::where('team_id',$request->team_id)->get();
			foreach($team_details as $team_detail)
			{
			    $team_detail->delete();
			}
            $team->delete();
            return $this->apiResponse(['message'=>'Successfully deleted team']);
            
        }
        catch(Exception $e)
        {
            return $this->apiResponse(['error'=>$e->getMessage()],true);
        }
    }
    
    public function editTeam(Request $request)
    {
        try{
            $header_token = $request->bearerToken();
            $model = User::where('token','=',$header_token)->first();
            if( !$model ) 
                return $this->apiResponse(['error'=>'Invalid token'],true);
            else if($model->role_id != 1)
                return $this->apiResponse(['error'=>'Token is not for Admin and TL'],true);

            $validator = Validator::make(
                    $request->all(),[
                        'team_name' => 'required',
                        'team_id'=>'required'
                    ]
            );
            if($validator->fails()) $this->apiResponse(['error'=>'validation is not fullfill'],true);
            $team = Team::where('id',$request->team_id)->first();
            $team->name = $request->team_name;
            $team->save();

            return $this->apiresponse(['message'=>'Successfully edit team name']);

        }
        catch(Exception $e)
        {
            return $this->apiResponse(['error'=>$e->getMessage()],true);
        }
    }
    
	public function addTeam(Request $request)
	{
		 try{
		 	$header_token = $request->bearerToken();
			$model = User::where('token','=',$header_token)->first();
			if( !$model ) 
				return $this->apiResponse(['error'=>'Invalid token'],true);
			else if($model->role_id != 1)
				return $this->apiResponse(['error'=>'Token is not for Admin and TL'],true);
				
            if($request->isMethod('post')){
    			$validator = Validator::make(
    		        $request->all(),[
    		            'name' => 'required',
    		        ]
    		    );
    			if($validator->fails()) $this->apiResponse(['error'=>'validation is not fullfill'],true);
                $team = Team::create(['name'=>$request->name,'created_userid'=>$model->id]);
    
                $id = \DB::getPdo()->lastInsertId();
    			$team_data = Team::findOrFail($id);
                if($team_data)
                {
            		return $this->apiResponse(['message' => 'Successfully team add', 'data' => $team_data]);
                }
            	else
            	{
            		return $this->apiResponse(['error'=>'data not Register'],true);
            	}
            }
            else if($request->isMethod('get')){
                $team_details = TeamDetail::all()->sortBy("team_id");
                $final = [];
                $teamid;
                $team_id = [];
                $except_team = [];
                $except_user = [];
                foreach($team_details as $team_detail)
                {
                    if(empty($team_id))
                    {
                        array_push($team_id,$team_detail->team_id);
                        $teamid = $team_detail->team_id;
                    }
                    else if($teamid != $team_detail->team_id)
                    {
                        array_push($team_id,$team_detail->team_id);
                        $teamid = $team_detail->team_id;
                    }
                }
                foreach($team_id as $id)
                {
                    $datas = TeamDetail::where('team_id',$id)->get();
                    $id = 0;
                    $count =0;
                    foreach($datas as $data)
                    {
                        $user = User::where('id',$data->user_id)->first();
                        if($user->role_id == '2')
                            array_push($except_user,$user->id);
                        // if($user->deleted != 1)
                        //     $count = $count + 1;
                        $count = $count + 1;
                        $id = $data->team_id;
                    }
                    $team = Team::where('id',$id)->first();
                    $user = User::where('id',$team->created_userid)->first();
                    array_push($except_team,$id);
                    $user_array = [];
                    $user_array['team_id'] = $team->id;
                    $user_array['team_name'] = $team->name;
                    $user_array['created_by'] = $user->name;
                    $user_array['number_of_member'] = $count;
                    $count = 1;
                    array_push($final,$user_array);
                }
                // print_r(json_encode($final[team_name]));
                // die;
                //$aa =[1];
                $datas = Team::all()->except($except_team);
                foreach($datas as $data)
                {
                    $team = Team::where('id',$data->id)->first();
                    $user = User::where('id',$team->created_userid)->first();
                    $user_array = [];
                    $user_array['team_id'] = $team->id;
                    $user_array['team_name'] = $team->name;
                    $user_array['created_by'] = $user->name;
                    $user_array['number_of_member'] = 0;
                    array_push($final,$user_array);
                }

                $tl_ids = User::select('id')->where('role_id','1')->orWhere('deleted','1')->get();
                foreach($tl_ids as $tl_id)
                    array_push($except_user,$tl_id->id);
                $users = User::all()->except($except_user);
                return $this->apiResponse(['message' => 'data of teams', 'data' => $final,'unassigned_user'=>$users]);
            }

			
		 }
		 catch(Exception $e)
		 {
		 	return $this->apiResponse(['error'=>$e->getMessage()],true);
		 }
	}
	
	public function addTeamMember(Request $request)
	{
		 try{
		 	$header_token = $request->bearerToken();
			$model = User::where('token','=',$header_token)->first();
			if( !$model ) 
				return $this->apiResponse(['error'=>'Invalid token'],true);
			else if($model->role_id != 1)
				return $this->apiResponse(['error'=>'Token is not for Admin and TL'],true);
            if($request->isMethod('post')){
    			$validator = Validator::make(
    		        $request->all(),[
    		            'team_id' => 'required',
    		            
    		        ]
    		    );
    			if($validator->fails()) $this->apiResponse(['error'=>'validation is not fullfill'],true);
    			foreach($request->user_id as $user_id)
    			{
    			    $team = TeamDetail::create(['user_id'=>$user_id,
                                            'team_id'=>$request->team_id]);    
    			}
    //             $id = \DB::getPdo()->lastInsertId();
    // 			$team_data = TeamDetail::findOrFail($id);
                $team_data = TeamDetail::where('team_id',$request->team_id)->get();
                if($team_data)
                {
            		return $this->apiResponse(['message' => 'Successfully team member add', 'data' => $team_data]);
                }
            	else
            	{
            		return $this->apiResponse(['error'=>'data not Register'],true);
            	}
            }

		 }
		 catch(Exception $e)
		 {
		 	return $this->apiResponse(['error'=>$e->getMessage()],true);
		 }
	}
	
	public function showTeamMember(Request $request)
	{
		 try{
		 	$header_token = $request->bearerToken();
			$model = User::where('token','=',$header_token)->first();
			if( !$model ) 
				return $this->apiResponse(['error'=>'Invalid token'],true);
			else if($model->role_id != 1)
				return $this->apiResponse(['error'=>'Token is not for Admin and TL'],true);
				
			$validator = Validator::make(
		        $request->all(),[
		            'team_id' => 'required',
		        ]
		    );
			if($validator->fails()) $this->apiResponse(['error'=>'validation is not fullfill'],true);
			
			$team_details = TeamDetail::where('team_id',$request->team_id)->get();
			$team_detail_array = [];
			foreach($team_details as $team_detail)
			{
			    $array = [];
			    $user = User::where('id',$team_detail->user_id)->first();
			    if($user->deleted == 1)
			        continue;
			    $array ['id'] = $team_detail->id;
			    $array ['team_id'] = $team_detail->team_id;
			    $array ['user_name'] = $user->name;
			    $array ['user_id'] = $team_detail->user_id;

			    array_push($team_detail_array,$array);
			    
			}
// 			print_r($team_detail_array);
// 			die;
			$user = User::select('id','name')->where('deleted','!=','1')->get();
			
			return $this->apiResponse(['message' => 'team member details', 'data' =>$team_detail_array,'user'=>$user]);
			
		 }
		 catch(Exception $e)
		 {
		 	return $this->apiResponse(['error'=>$e->getMessage()],true);
		 }
	}
	
}