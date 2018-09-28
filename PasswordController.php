<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

use App\User;
use Gate;
use Cache;
use Session;
use DB;
use Validator;
use Redirect;
use Response;
use Mail;
use Languagehelper;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;


class PasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Create a new password controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
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
					//echo $password_link; exit();
					//echo  url(['for':'/resetpassword/','params':crypt.encrypt($user->id)]); exit();
					$sent = Mail::send('auth.emails.password', ['users' => $users,'password_link' => $password_link ], function($message) use ($users){
					$message->from('timo.linsenmaier@ei-ie.org');
					//$message->from('headoffice@ei-ie.org');	
					$message->to(trim($users->email),trim($users->firstname));//->subject('Your New Password');
					$message->replyTo('headoffice@ei-ie.org');
					$message->subject('Education International- Hey, did you forget your password? Click the link to reset it.');
					
				});
				
				if(!$sent){
					return Response::json(array('success' => false,'data'=>'','response_fail'=>"Sorry! Your message has not been sent because of a technical error."), 200);
				}else{
					///User::where('id', $user->id)->update([  'password' => bcrypt($randPassword),]);
					return Response::json(array('success' => true,'data'=>'','response_sucess'=>'Password is send to your email address.'), 200);	
				}				
				return Response::json(array('success' => false,'data'=>'','response_fail'=>"Sorry! Your message has not been sent because of a technical error."), 200);
			}
		}
	}
	function resetpassword($id){		
		$ids = Crypt::decrypt($id); 
		$users = User::findOrFail($ids);	
	    return view('auth.resetpassword',['users'=>$users]);	
	}
	function setpassword(Request $request,$id){	
		//echo "hiii"; exit();
		$ids = Crypt::encrypt($id);
		
		
		$fields = array(
			'password'=>trim(Input::get('password')),
			'password_confirmation'=>trim(Input::get('password_confirmation')),						
		);
		$rules = array(       
			'password' => 'required',
			//'password_confirmation' => 'required',
			'password_confirmation' => 'required|same:password',
	
		);
		// Create a new validator instance.
		$validator = Validator::make($fields,$rules);		
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
		
	function getRandomPassword($length, $start = 1, $end = 36)
	{
		if($length > 0)
		{
			$rand_id = '';
			for($i = 1; $i <= $length; $i++)
			{
				mt_srand((double)microtime() * 1000000);
				$num = mt_rand($start,$end);
				$rand_id .= $this->assign_rand_value($num);
			}
		}
		return $rand_id;
	} // End of Function
	//Function to generate random verification code number	
	function assign_rand_value($num)
	{
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
	} // End of Function	
	
}
