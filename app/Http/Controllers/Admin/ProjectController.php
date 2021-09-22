<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Team, App\User, App\TeamDetail, App\Project, App\ProjectDetail;
use \Validator;
use \DB;

class ProjectController extends \App\Http\Controllers\Controller
{
	public function reportDropDownTeam(Request $request)
	{
		try{
			$header_token = $request->bearerToken();
			$user = User::where('token','=',$header_token)->first();
			if( !$user ) 
				return $this->apiResponse(['error'=>'Invalid token'],true);
			else if($user->role_id != 1)
				return $this->apiResponse(['error'=>'Token is not for Admin and TL'],true);
			$teams =Team::all();
			return $this->apiResponse(['message'=>'All teams','data'=>$teams]);
		}
		catch(Exception $e)
		{
			return $this->apiResponse(['error'=>$e->getMessage()],true);
		}
	}

	public function reportProjectManagement(Request $request)
	{
		try{
			$header_token = $request->bearerToken();
			$user = User::where('token','=',$header_token)->first();
			if( !$user ) 
				return $this->apiResponse(['error'=>'Invalid token'],true);
			else if($user->role_id != 1)
				return $this->apiResponse(['error'=>'Token is not for Admin and TL'],true);
			$team_id = $request->filter_by_team;
			if($team_id == null)
			{
				$final = [];
				$teams = Team::all();
				foreach ($teams as $team) {
					$projects = Project::where('team_id',$team->id)->get();
					foreach ($projects as $project) {
						$user = User::where('id',$project->tl_userid)->first();
						$team = Team::where('id',$team->id)->first();
						$members = ProjectDetail::where('project_id',$project->id)->count();
						$project_array = [];
						$project_array['team_name'] = $team->name;
						$project_array['project_name'] = $project->name;
						if($user != null)
							$project_array['tl_name'] = $user->name;
						else
							$project_array['tl_name'] = null;
						$project_array['members_count'] = $members;
						array_push($final,$project_array);
					}
				}
				return $this->apiResponse(['message'=>'project report','data'=>$final]);
			}
			else
			{
				$final = [];
				$projects = Project::where('team_id',$team_id)->get();
				foreach ($projects as $project) {
					$user = User::where('id',$project->tl_userid)->first();
					$team = Team::where('id',$team_id)->first();
					$members = ProjectDetail::where('project_id',$project->id)->count();
					$project_array = [];
					$project_array['team_name'] = $team->name;
					$project_array['project_name'] = $project->name;
					if($user != null)
						$project_array['tl_name'] = $user->name;
					else
						$project_array['tl_name'] = null;
					$project_array['members_count'] = $members;
					array_push($final,$project_array);
				}
				return $this->apiResponse(['message'=>'project report','data'=>$final]);
			}
		}
		catch(Exception $e)
		{
			return $this->apiResponse(['error'=>$e->getMessage()],true);
		}
	}

	public function addProject($team_id,Request $request)
	{
		try{
			    $header_token = $request->bearerToken();
				$user = User::where('token','=',$header_token)->first();
				if( !$user ) 
					return $this->apiResponse(['error'=>'Invalid token'],true);
				else if($user->role_id != 1)
					return $this->apiResponse(['error'=>'Token is not for Admin and TL'],true);
				if($request->isMethod('post')){
					Project::create(['team_id'=>$request->team_id,'name'=>$request->project_name]);
					$id = \DB::getPdo()->lastInsertId();
					$project = Project::findOrFail($id);
					return $this->apiResponse(['message'=>'successfully add project','data'=>$project]);
				}
				else if($request->isMethod('get'))
				{
					$projects = Project::where('team_id',$team_id)->get();
					return $this->apiResponse(['message'=>'projects of respective team','data'=>$projects]);
				}
		}
		catch(Exception $e)
		{
			return $this->apiResponse(['error'=>$e->getMessage()],true);
		}
	}
	public function deleteProject(Request $request)
	{
		try{
			$header_token = $request->bearerToken();
			$user = User::where('token','=',$header_token)->first();
			if( !$user ) 
				return $this->apiResponse(['error'=>'Invalid token'],true);
			else if($user->role_id != 1)
				return $this->apiResponse(['error'=>'Token is not for Admin and TL'],true);
			$project = Project::find($request->project_id);
			$project->delete();

			ProjectDetail::where('project_id',$request->project_id)->delete();
			

			return $this->apiResponse(['messgae'=>'successfully project deleted']);
		}
		catch(Exception $e)
		{
			return $this->apiResponse(['error'=>$e->getMessage()],true);
		}
	}

	public function deleteProjectManagement(Request $request)
	{
		try{
			$header_token = $request->bearerToken();
			$user = User::where('token','=',$header_token)->first();
			if( !$user ) 
				return $this->apiResponse(['error'=>'Invalid token'],true);
			else if($user->role_id != 1)
				return $this->apiResponse(['error'=>'Token is not for Admin and TL'],true);
			
			$project_detail = ProjectDetail::where('user_id',$request->user_id)
										->where('project_id',$request->project_id)
										->first();
			$project_detail->delete();
	
			return $this->apiResponse(['message'=>'successfully developer delete']);
		}
		catch(Exception $e)
		{
			return $this->apiResponse(['error'=>$e->getMessage()],true);
		}
	}

	public function projectManagement($team_id,$project_id,Request $request)
	{
		try{
			$header_token = $request->bearerToken();
			$user = User::where('token','=',$header_token)->first();
			if( !$user ) 
				return $this->apiResponse(['error'=>'Invalid token'],true);
			else if($user->role_id != 1)
				return $this->apiResponse(['error'=>'Token is not for Admin and TL'],true);
			if($request->isMethod('get')){
				$user_array = [];
				$team_details = TeamDetail::where('team_id',$team_id)->get();

				foreach ($team_details as $team_detail) {
					// $project = ProjectDetail::where('user_id',$team_detail->user_id)->where('project_id',)->get();
					// print_r(json_encode($project));
					// die;
					// if(!$project)
					// {
					// 	$user = User::select(DB::raw('id as user_id'),'name','role_id')->where('role_id','2')
					// 										->where('id',$team_detail->user_id)
					// 										->first();
					// 	if($user != null)									
					// 	 array_push($user_array,$user);
					// }
					$user = User::select(DB::raw('id as user_id'),'name','role_id')->where('role_id','2')
														->where('id',$team_detail->user_id)
														->first();
					if($user != null)									
					 array_push($user_array,$user);
				}
				$admin_tl = User::select(DB::raw('id as user_id'),'name','role_id')->where('role_id','1')->get();
				$project = Project::where('id',$project_id)->first();
				if($project->tl_userid)
					$user = User::where('id',$project->tl_userid)->first();
				$project_details = [];
				$project_details['team_id'] = $team_id;
				$project_details['project_id'] = $project_id;
				$project_details['tl_name'] = null;
				$project_details['tl_user_id'] = null;
				if($project->tl_userid)
					$project_details['tl_name'] = $user->name;
				if($project->tl_userid)
					$project_details['tl_user_id'] = $user->id;
				$project_details['technology'] = $project->technology;
				$out_array = [];
				$details = ProjectDetail::where('project_id',$project_id)->get();
				foreach($details as $detail)
				{
					$user = User::where('id',$detail->user_id)->first();
					// if($user->role_id == 1)        //only for when one TL or Admin
					// {
					// 	$project_details['tl_name'] = $user->name;
					// 	$project_details['tl_tech'] = $detail->technology;
					// }
					$tem_array = [];
					$tem_array['user_name'] = $user->name;
					$tem_array['user_id'] = $user->id;
					$tem_array['dev_tech'] = $detail->technology;
					array_push($out_array,$tem_array);

				}
				$project_details['dev_info'] = $out_array;


				
				return $this->apiResponse(['message'=>'list of employee and TL or Admin of his team',
											'users_data'=>$user_array,
											'tl_data'=>$admin_tl,
											'project_data'=>$project_details
											
																		]);
			}
			else if($request->isMethod('post'))
			{
				$project = Project::where('id',$project_id)->first();
				$project->tl_userid = $request->tl_userid;
				$project->technology = $request->technology;
				$project->save();
				foreach($request->dev_info as $dev_info)
				{
					ProjectDetail::create(['project_id'=>$request->project_id,
											'user_id'=>$dev_info['user_id'],
											'technology'=>$dev_info['dev_tech']
										]);					
				}
				return $this->apiResponse(['message'=>'successfully added project managment detail']);
				
			}
		}
		catch(Exception $e)
		{
			return $this->apiResponse(['error'=>$e->getMessage()],true);
		}
	}
}