<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Validator;
use App\User;
use \DB;
use Auth;
use Response;
use Mail;
use \ConvertApi\ConvertApi;
ConvertApi::setApiSecret('bFYdG835WgSk1RfU');

class LoginController extends Controller
{
    
	public function resumePdfPath(Request $request)
    {
        try{
            $header_token = $request->bearerToken();
			$user = User::where('token','=',$header_token)->first();
		    if( !$user ) 
		        return $this->apiResponse(['error'=>'Invalid token'],true);
		    
            $resume = $user->resume;
            if($resume != null){
                $val = basename($resume,'.doc');
    
                $path = ['full_path'=>'http://'.$_SERVER['SERVER_NAME'].'/resume/'];
                
                $pdf_destination = $path['full_path'].$val.'.pdf';
                
                return $this->apiResponse(['message'=>'files info','Pdf_file'=>$pdf_destination]);
            }
            else
            {
                return $this->apiResponse(['message'=>'resume not present for this employee']);
            }
            
        }
        catch(Exception $e)
        {
            return $this->apiResponse(['error'=>$e->getMessage()],true);
        }
    }

    public function update(Request $request) 
	{
		try
		{
		    $header_token = $request->bearerToken();
			$model = User::where('token','=',$header_token)->first();
		    if( !$model ) return $this->apiResponse(['error'=>'Invalid token'],true);

            if($request->isMethod('post')){
                if(isset($request->name)  || !empty($request->name))
                    $model->name = $request->name;
                if(isset($request->email)  || !empty($request->email))    
                    $model->email = $request->email;
                if(isset($request->password)  || !empty($request->password))
                    $model->password = bcrypt($request->password);
                if(isset($request->mobile_number)  || !empty($request->mobile_number))
                    $model->mobile_number = $request->mobile_number;
                if(isset($request->skills)  || !empty($request->skills))
                    $model->skills = $request->skills;
            
                if ($request->file('resume')) {
                    //save doc file in /public/resume folder
                     $destinationPath = public_path()."/resume"; // upload path
                    $path = ['full_path'=>'http://'.$_SERVER['SERVER_NAME'].'/resume/',
                                'rel_path'=>'resume',
                                'default_resume'=>'http://'.$_SERVER['SERVER_NAME'].'/resume/no-resume.doc'];
                                
                    $profileImage = date('YmdHis') . "." . $request->file('resume')->getClientOriginalExtension();
                    $request->file('resume')->move($destinationPath, $profileImage);
                    $model->resume = $path['full_path'].$profileImage;
                    $extension = $request->file('resume')->getClientOriginalExtension();

                    if ($extension == 'doc'){
                        $file_name = basename($profileImage,'.doc');
                        $result = ConvertApi::convert('pdf', ['File' => public_path().'/resume/'.$profileImage]);
                        //  save to file
                        $result->getFile()->save(public_path().'/resume/'.$file_name.'.pdf');
                    }
                }
                $model->save();
                return $this->apiResponse(['message'=>'Successfully update data']);
            }
            else if($request->isMethod('get'))
            {
                // return $this->apiResponse(['data'=>$model]);
                
                $array = [];
                $array['user_name'] = $model->name;
                $array['email'] = $model->email;
                $array['password'] = ($model->password);
                $array['mobile_number'] = $model->mobile_number;
                $array['skills'] = $model->skills;
                $array['resume'] = $model->resume;
                return $this->apiResponse(['data'=>$array]);
                
                
            }
            
        } catch(\Exception $e) {
            return $this->apiResponse(['error'=>$e->getMessage()],true);
        }
	}
    
	public function emailVerified(Request $request)
	{
		try{
			$header_token = $request->bearerToken();
			$model = User::where('token','=',$header_token)->first();
			if( !$model ) return $this->apiResponse(['error'=>'Invalid token'],true);
			$model->verified = 1;
			$model->save();
			return $this->apiResponse(['message'=>'Successfully email verify']);
		}
		catch(Exception $e)
		{
			return $this->apiResponse(['error'=>$e->getMessage()],true);
		}
	}

	public function resetPassword(Request $request)
	{
		try {
			$header_token = $request->bearerToken();
			$model = User::where('token','=',$header_token)->first();
		    if( !$model ) return $this->apiResponse(['error'=>'Invalid token'],true);
		    //try validation the user request
 		    $validator = Validator::make(
            	$request->all(),[
                	'password' => 'required'
            		]
        		);
		    if($validator->fails()) {
		            return $this->apiResponse(['error' => $validator->messages()->first()], true);
		        }
		    $model->password = bcrypt($request->password);
		    $model->token = md5(uniqid($model->email, true));
		    $model->save();   
		    return $this->apiResponse(['message'=>'successfull password resset']);
			} catch(\Exception $e) {
				return $this->apiResponse(['error'=>'Invalid login credentials'],true);
			}
	}

	public function forgotPasswordFunction(Request $request){
		try{
			$inputs = $request->all();
			$validator = Validator::make(
            	$request->all(),[
                	'email' => 'required|email'
            		]
        		);
	       	if($validator->fails()) {
	            return $this->apiResponse(['error' => $validator->messages()->first()], true);
	        }
			if(!isset($inputs['email'])  || empty($inputs['email']))
			{
				return $this->apiResponse(['error' => 'email  not be null'], true);
			}
			else
			{
				$data = User::where('email','=',$inputs['email'])->first();
				if( !$data )
			 	{
			 		return $this->apiResponse(['error' => 'User not Exist'], true);
			 	}
			 	else
			 	{

				    $data1 = $data->toArray();
				    $data2 =[];
				    $data2['user'] = $data1;
			        Mail::send('emails.password', $data2, function($message) use ($data2) {
				        $message->to($data2['user']['email']);
				        $message->subject('Please Reset your password for Enuke EMS Portal');
			      	});
			        if (Mail::failures()) {
			           return $this->apiResponse(['error'=>'mail not send'],true);
			         }else{
			           return $this->apiResponse(['success' => 'true' ,'message' => 'successfull send the link on this email']);
			         }
			 	}
			}
		}
		catch(\Exception $e) {
			return $this->apiResponse(['error' => $e->getMessage()], true);
		}
	}

	public function login(Request $request)
	{
		try {
		    $validator = Validator::make(
		        $request->all(),[
		            'email' => 'email',
		            'password' => 'required'
		        ]
		    );
		    if($validator->fails()) return $this->apiResponse(['error'=>'validation is not fullfill'],true);
		    $model = User::where('email', $request->email)->first();
		    if( !$model ) return $this->apiResponse(['error'=>'Invalid login credentials'],true);
		    if($model->verified != 1)
		    	return $this->apiResponse(['error'=>'Given email is not verified'],true);
		    if($model->deleted == 1)
		        return $this->apiResponse(['error'=>'Your account is suspended by TL.'],true);
		    //try logging in the user
		    if(Auth::attempt($request->only(['email', 'password']), $request->input('remember'))) {
		      	$user = Auth::user();
		      	return $this->apiResponse(['message' => 'Successfully login', 'data' => $user]);
		    }
		    return $this->apiResponse(['error'=>'Invalid login credentials'],true);
		} catch(\Exception $e) {
			return $this->apiResponse(['error'=>'Invalid login credentials'],true);
		}
	}

	public function register(Request $request) 
	{
		try
		{
			$validator = Validator::make(
		        $request->all(),[
		            'email' => 'required',
		            'password' => 'required',
		            'name' => 'required',
		            'mobile_number'=>'required'
		        ]
		    );
			if($validator->fails()) $this->apiResponse(['error'=>'validation is not fullfill'],true);
            $user1 = User::create(['email'=>$request->email,
            						'password' => bcrypt($request->password),
            						'name' => $request->name, 
            						'mobile_number' =>$request->mobile_number
        						]);

            $id = \DB::getPdo()->lastInsertId();
			$user = User::findOrFail($id);

            if($user)
            {
            	$token = md5(uniqid($request->email, true));
            	$user->token = $token;
            	$user->role_id = '2';
            	$user->save();

			    $data1 =[];
			    $data1['email'] = $request->email;
			    $data1['name'] = $request->name;
			    $data1['token'] = $token;
			    $data2 = [];
			    $data2['user'] = $data1;

		        Mail::send('emails.register', $data2, function($message) use ($data2) {
			        $message->to($data2['user']['email']);
			        $message->subject('[Enuke EMS Portal] Confirm E-mail Address');
		      	});
		        if (Mail::failures()) {
		           return $this->apiResponse(['error'=>'mail not send'],true);
		         }
        		return $this->apiResponse(['message' => 'Successfully send the link on email for verify Register', 'data' => $user]);
            }
        	else
        	{
        		return $this->apiResponse(['error'=>'data not Register'],true);
        	}
        } catch(\Exception $e) {
            return $this->apiResponse(['error'=>$e->getMessage()],true);
        }
	}
}
