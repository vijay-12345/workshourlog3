<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\User, App\UserLeave, App\Task;
use Config;
use Illuminate\Support\Facades\Storage;
use File;
use PDF;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings as WordSettings;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Writer\Word2007\Element\Container;
use PhpOffice\Common\XMLWriter;
//use NahidulHasan\Html2pdf\Facades\Pdf;
use NcJoes\OfficeConverter\OfficeConverter;
use \ConvertApi\ConvertApi;
ConvertApi::setApiSecret('bFYdG835WgSk1RfU');


class LoginController extends \App\Http\Controllers\Controller 
{
    
    public function resumePdfPath(Request $request)
    {
        try{
            $header_token = $request->bearerToken();
			$model = User::where('token','=',$header_token)->first();
		    if( !$model ) 
		        return $this->apiResponse(['error'=>'Invalid token'],true);
		    else if($model->role_id != 1)
		    {
		        return $this->apiResponse(['error'=>'Token is not for Admin or TL'],true);
		    }
		    
		    $user_id = $request->user_id;

            $user = User::where('id',$user_id)->first();
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
    
    public function update($user_id,Request $request) 
	{
		try
		{
		    $header_token = $request->bearerToken();
			$model = User::where('token','=',$header_token)->first();
		    if( !$model ) 
		        return $this->apiResponse(['error'=>'Invalid token'],true);
		    else if($model->role_id != 1)
		    {
		        return $this->apiResponse(['error'=>'Token is not for Admin or TL'],true);
		    }
		    
		    $user = User::where('id',$user_id)->first();

            if($request->isMethod('post')){
                if(isset($request->name)  || !empty($request->name))
                    $user->name = $request->name;
                if(isset($request->email)  || !empty($request->email))    
                    $user->email = $request->email;
                if(isset($request->password)  || !empty($request->password))
                    $user->password = bcrypt($request->password);
                
                if(isset($request->mobile_number)  || !empty($request->mobile_number))
                    $user->mobile_number = $request->mobile_number;
                if(isset($request->skills)  || !empty($request->skills))
                    $user->skills = $request->skills;
                    
                    
                if ($request->file('resume')) {
                    // $image_path = $user->resume;
                    // //unlink(public_path()."/resume/20200413014953.doc");
                    //   if (File::exists($image_path)) {
                    //     //File::delete($image_path);
                    //     unlink($image_path);
                    
                    // }

                    //save doc file in /public/resume folder
                     $destinationPath = public_path()."/resume"; // upload path
                    $path = ['full_path'=>'http://'.$_SERVER['SERVER_NAME'].'/resume/',
                                'rel_path'=>'resume',
                                'default_resume'=>'http://'.$_SERVER['SERVER_NAME'].'/resume/no-resume.doc'];
                    $profileImage = date('YmdHis') . "." . $request->file('resume')->getClientOriginalExtension();
                    $request->file('resume')->move($destinationPath, $profileImage);
                    $user->resume = $path['full_path'].$profileImage;
                    $extension = $request->file('resume')->getClientOriginalExtension();
                    // if ($extension == 'doc') {
                        
                    //     $file_name = basename($profileImage,'.doc');
                    //     $converter = new OfficeConverter(public_path().'/resume/'.$profileImage);
                    //     $converter->convertTo($file_name.'.pdf');
                    // }
                    if ($extension == 'doc'){
                        $file_name = basename($profileImage,'.doc');
    
                        $result = ConvertApi::convert('pdf', ['File' => public_path().'/resume/'.$profileImage]);
    
                        # save to file
                        $result->getFile()->save(public_path().'/resume/'.$file_name.'.pdf');
                    }
                                        
                }
                
                $user->save(); 
                return $this->apiResponse(['message'=>'Successfully update data']);
            }
            else if($request->isMethod('get'))
            {
                $path = ['full_path'=>'http://'.$_SERVER['SERVER_NAME'].'/resume/',
                    'rel_path'=>'resume',
                    'default_resume'=>'http://'.$_SERVER['SERVER_NAME'].'/resume/no-resume.doc'];
                //return $this->apiResponse(['data'=>$user]);
                
                
                $array = [];
                $array['user_name'] = $user->name;
                $array['email'] = $user->email;
                $array['mobile_number'] = $user->mobile_number;
                $array['skills'] = $user->skills;
                $array['resume'] = $user->resume;
                $array['resume_template'] = $path['full_path'].'resume_template.doc';
                return $this->apiResponse(['data'=>$array]);
                
                
            }
            
        } catch(\Exception $e) {
            return $this->apiResponse(['error'=>$e->getMessage()],true);
        }
	}
}
