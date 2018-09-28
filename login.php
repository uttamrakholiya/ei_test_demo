<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Crypt;
use DB;
use Validator;
use Redirect;
use Response;
use Mail;
use App\User;

class LoginController extends Controller {  
    
	public function showLogin(){		
		return view('admin.login');
	}
	
	public function doLogin(Request $request){				
		$messages = [
			'required' => 'The :attribute is required.',				
		];
		$fields = array(
			'username'=>trim($request->get('username')),
			'password'=>trim($request->get('password')),						
		);
		if($request->has('username')){			
			$subsite_id=DB::table('users')->where('username', $request->get('username'))->first();                               
			$sbid=(empty($subsite_id)) ? 0 : $subsite_id->subsite_id;            
			$statuse="";
           
            if($sbid!=0){  
                $user_login=DB::table('users')->where('username', $request->get('username'))->count();       
                if($user_login==1){
                    $loginDate= date('Y-m-d');
                    $loginTime= date('Y-m-d H:i:s');                    
                    DB::table('site_log')->insert([
                        'subsite_id'=>$subsite_id->subsite_id,
                        'user_id'=>$subsite_id->id,
                        'role_id'=>$subsite_id->role_id,
                        'logindate'=>$loginDate,
                        'logintime'=>$loginTime             
                    ]);
                }                
                
                $status=DB::table('subsites')->where('id', $sbid)->first();              
                if(!empty($status)){
                    $statuse=$status->status;
                }
            }
			
            $messages =['subsite_id.required' => 'The subsite user is Inactive.',];
            
            if(($statuse!="" && $statuse == 0) && $sbid!=0){                                
				$validator = Validator::make($fields, ['subsite_id' => 'required','password' => 'required',],$messages);
            }else{
				$rules = array(
					'username' => 'required|exists:users,username,is_active,1',
                    'password' => 'required',
				);               
				$validator = Validator::make($fields,$rules,$messages);
            }
        }else{			
			$rules = array(
				'username' => 'required|exists:users,username,is_active,1',
                'password' => 'required',
			);            
			$validator = Validator::make($fields,$rules,$messages);
        }
		
		if ($validator->fails()) {			
			return Redirect::to('/login')->withInput($fields)->withErrors($validator);
		}else{
			$credentials = $request->only('username','password');
			if (Auth::attempt($credentials)){
				return Redirect::to('/index');
			}else{							
				return Redirect::back()->withInput($request->only('username', 'remember'))
									->withErrors([ 'username' => 'These credentials do not match our records.', ]);				
			}			
		}
	}
	
	public function doLogout(){
		$login_user = Auth::user();
		//print_r($login_user);
        $subsite_id=$login_user->subsite_id;
        $user_id=$login_user->id;
        $role_id=$login_user->role_id;
        $siteLogDate= date('Y-m-d');
                
        $login_data=DB::table('site_log')
            ->where('subsite_id',$subsite_id)
            ->where('user_id',$user_id)
            //->where('role_id',$role_id)
            ->where('logindate',$siteLogDate)
            ->orderBy('id','DESC')
            ->first();
        //echo "<pre>"; print_r($login_data); exit()
        if(!empty($login_data)){            
            $Id= $login_data->id;
            //print_r($Id); exit();
            $loginTime= $login_data->logintime;
            $totalTime1= $login_data->totaltime;
        }else{
        	//echo "hiiii"; exit();
			$logind=DB::table('site_log')
            ->where('subsite_id',$subsite_id)
            ->where('user_id',$user_id)
            //->where('role_id',$role_id)
            ->orderBy('id','DESC')
            ->first(); 
          // echo "<pre>"; print_r($logind); exit();
            $Id = $logind->id;
           // print_r($Id); exit();
            $loginTime= $logind->logintime;
            $totalTime1= $logind->totaltime;
        }
        
        $logoutTime= date('Y-m-d H:i:s');
        
        $diff_set_id = strtotime($logoutTime) - strtotime($loginTime);
        $diff = abs($diff_set_id);
        $years   = floor($diff / (365*60*60*24)); 
        $months  = floor(($diff - $years * 365*60*60*24) / (30*60*60*24)); 
        $days    = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
        $hours   = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60)); 
        $minuts  = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ 60); 
        $seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minuts*60));
        $totalTime= $hours.":".$minuts.":".$seconds;        
        $siteLogTotalTime= $totalTime;
        
        DB::table('site_log')
        ->where('id',$Id)
        ->where('subsite_id',$subsite_id)
        ->where('user_id',$user_id)
        //->where('role_id',$role_id)
        ->where('logindate',$siteLogDate)
        ->update([              
            'logouttime'=>$logoutTime,
            'totaltime'=>$siteLogTotalTime            
        ]);        
		Auth::logout(); 
		return Redirect::to('/login'); 
	}
	
	public function forgotPassword(){		
		//print_r(Auth::user()); exit();
		$messages = [
			'required' => 'The email is required.',
			'exists'=>'The `'.trim(Input::get('email_address')).'` is not exists.',
		];

		$fields = array('email_address'=>trim(Input::get('email_address')),);
		$rules = array('email_address' => 'required|email|exists:users,email',);
        $validator = Validator::make($fields, $rules,$messages);        
		if ($validator->fails()){   
			return Response::json(array(
				'success' => false,
				'errors' => $validator->getMessageBag()->toArray()
			), 400);			 
		} else {
			$user = User::where('email', '=', Input::get('email_address'))->first();
			$randPassword=$this->getRandomPassword(8);
			if($user){	

					$users = User::findOrFail($user->id);								
				    //$sent = Mail::send('admin.password', ['users' => $users,'randPassword' => $randPassword ], function($message) use ($users){
					//$password_link = '/resetpassword/'.Crypt::encrypt($user->id);
					$userid = Crypt::encrypt($user->id);
					$password_link = url('/resetpassword/'.$userid);  

					//echo  url(['for':'/resetpassword/','params':crypt.encrypt($user->id)]); exit();
					$sent = Mail::send('admin.password', ['users' => $users,'password_link' => $password_link ], function($message) use ($users){
					$message->from('timo.linsenmaier@ei-ie.org');
					//$message->from('headoffice@ei-ie.org');	
					$message->to(trim($users->email),trim($users->firstname));//->subject('Your New Password');
					$message->replyTo('headoffice@ei-ie.org');
					$message->subject('Education International- Hey, did you forget your password? Click the link to reset it.');
					
				});
				
				if(!$sent){
					return Response::json(array('success' => true,'data'=>'','response_fail'=>"Sorry! Your message has not been sent because of a technical error."), 200);
				}else{
					///User::where('id', $user->id)->update([  'password' => bcrypt($randPassword),]);
					return Response::json(array('success' => true,'data'=>'','response_sucess'=>'Password is send to your email address.'), 200);	
				}				
				return Response::json(array('success' => true,'data'=>'','response_fail'=>"Sorry! Your message has not been sent because of a technical error."), 200);
			}
		}
	}	
	function resetpassword($id){		
		$ids = Crypt::decrypt($id); 
		$users = User::findOrFail($ids);	
	    return view('admin.resetpassword',['users'=>$users]);	
	}
	function setpassword(Request $request,$id){	
		$ids = Crypt::encrypt($id);
		$rules = array(       
			'password' => 'required',
			//'password_confirmation' => 'required',
			'password_confirmation' => 'required|same:password'
		);
		$fields = array(
			'password'=>trim(Input::get('password')),
			'password_confirmation'=>trim(Input::get('password_confirmation')),						
		);
		// Create a new validator instance.
		$validator = Validator::make($fields, $rules);		
		if ($validator->fails()){  			
			return Redirect::to('/resetpassword/'.$ids)->withInput($fields)->withErrors($validator);

		}else{	
		// echo "<pre>"; print_r(Input::get()); exit();		
			$users = User::findOrFail($id);	
			$randPassword=Input::get('password');
			User::where('id', $users->id)->update(['password' => bcrypt($randPassword),]);
			$request->session()->flash('resetpasswordmessage', 'password updated successfully');	
			return Redirect::to('/login'); 	
		}
	}

	
	function getRandomPassword($length, $start = 1, $end = 36){
		if($length > 0){
			$rand_id = '';
			for($i = 1; $i <= $length; $i++){
				mt_srand((double)microtime() * 1000000);
				$num = mt_rand($start,$end);
				$rand_id .= $this->assign_rand_value($num);
			}
		}
		return $rand_id;
	} 
	//Function to generate random verification code number	
	function assign_rand_value($num){
		// accepts 1 - 36
		switch($num)
		{
			case "1":
				$rand_value = "a";
			break;
			case "2":
				$rand_value = "b";
			break;
			case "3":
				$rand_value = "c";
			break;
			case "4":
				$rand_value = "d";
			break;
			case "5":
				$rand_value = "e";
			break;
			case "6":
				$rand_value = "f";
			break;
			case "7":
				$rand_value = "g";
			break;
			case "8":
				$rand_value = "h";
			break;
			case "9":
				$rand_value = "i";
			break;
			case "10":
				$rand_value = "j";
			break;
			case "11":
				$rand_value = "k";
			break;
    		case "12":
				$rand_value = "b";
			break;
			case "13":
				$rand_value = "m";
			break;
			case "14":
				$rand_value = "n";
			break;
			case "15":
				$rand_value = "o";
			break;
			case "16":
				$rand_value = "p";
			break;
			case "17":
				$rand_value = "q";
			break;
			case "18":
				$rand_value = "r";
			break;
			case "19":
				$rand_value = "s";
			break;
			case "20":
				$rand_value = "t";
			break;
			case "21":
				$rand_value = "u";
			break;
			case "22":
				$rand_value = "v";
			break;
			case "23":
				$rand_value = "w";
			break;
			case "24":
				$rand_value = "x";
			break;
			case "25":
				$rand_value = "y";
			break;
			case "26":
				$rand_value = "z";
			break;
			case "27":
				$rand_value = "0";
			break;
			case "28":
				$rand_value = "b";
			break;
			case "29":
				$rand_value = "2";
			break;
			case "30":
				$rand_value = "3";
			break;
			case "31":
				$rand_value = "4";
			break;
			case "32":
				$rand_value = "5";
			break;
			case "33":
				$rand_value = "6";
			break;
			case "34":
				$rand_value = "7";
			break;
			case "35":
				$rand_value = "8";
			break;
			case "36":
				$rand_value = "9";
			break;
		}
		return $rand_value;
	} 	
}
