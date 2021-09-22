<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Validator;
use App\User, App\Task, App\UserLeave,App\Project,App\TeamDetail;
use \DB;
use Auth;
use Response;

class ProjectController extends Controller
{
	public function projectList(Request $request)
	{
		try{
			$header_token = $request->bearerToken();
			$user = User::where('token','=',$header_token)->first();
			if( !$user ) 
				return $this->apiResponse(['error'=>'Invalid token'],true);
			$team_details = TeamDetail::where('user_id',$user->id)->get();
			$project_list = [];
			foreach($team_details as $team_detail)
			{
				$projects = Project::where('team_id',$team_detail->team_id)->get();
				array_push($project_list,$projects);
			}
			return $this->apiResponse(['message'=>'projects list of employee','data'=>$project_list]);
		}
		catch(Exception $e)
		{
			return $this->apiResponse(['error'=>$e->getMessage()],true);
		}
	}
}