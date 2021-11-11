<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Firebase\JWT\JWT;


class UserController extends Controller
{
    //User Registration
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password)]
                ));

                //dd($user);
                UserController::sendEmail($request->name,$request->email);


        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }


    // Send Email
    public static function sendEmail($name, $email){
        $user = [
            'name' => $name,
            'info' => 'Press the Following Link to Verify Email',
            'Verification_link'=>url('api/verifyEmail/'.$email)
        ];
        \Mail::to($email)->send(new \App\Mail\NewMail($user));


    }


    // verification
    public function verify($email)
            {
                if(User::where("email",$email)->value('verify') == 1)
                {
                    $m = ["Your account has been verified"];
                    return response()->json($m);
                }
                else
                {
                    $update=User::where("email",$email)->update(["verify"=>1, "email_verified_at"=>date('Y-m-d H:i:s')]);
                    if($update){
                        return "Your Account has beem verified";
                    }else{
                        return false;
                    }
                }
            }


    // User Login
    public function login(Request $request){
        // dd($request);
    	if (Auth::attempt(['email'=> $request->email, 'password'=> $request->password]))
        {

            $user = Auth::user();
            $user_data = array(
                "id"=>$user->id,
                "name"=>$user->name,
                "email"=>$user->email);
                // check condition for verified email
                if(User::where("email",$user->email)->value('verify') == 1)
                {

                    $iss = "localhost";
                    $iat = time();
                    $nbf = $iat+10;
                    $exp = $iat+1800;
                    $aud = "User";
                    $payload_info= array(
                    "iss" =>$iss,
                    "iat" =>$iat,
                    "nbf" =>$nbf,
                    "exp" =>$exp,
                    "aud" =>$aud,
                    "data" =>$user_data
                    );
                    $key ='example_key';
                    $jwt=jwt::encode($payload_info,$key);
                     $user->jwt_token=$jwt;
                     User::where("email",$user->email )->update(["jwt_token"=>$jwt]);

                     $success['message']="User Succesfully Loged In";
                     $success['Authentication'] = $jwt;

                    return response()->json([
                   'message' => 'User successfully Loged In',
                   'user' => $user
                    ], 200);

                }
                else
                {
                    return response()->json([
                        'message' => 'User email not verified Please Check Your email to verify',
                        // 'user' => $user
                    ], 400);

                    // verify($user->email);

                    // call Method to send email Verification
                    UserController::sendEmail($user->name,$user->email);

                }



        }
        else
        {
            return response()->json([
                'message' => 'User Not Found',

            ], 404);
        }



    }
    // logout

    public function logout(Request $request) {
        $token = $request->bearerToken();
        // dd($token);

        $delete=User::where("jwt_token",$token)->update(["jwt_token"=>NULL]);
        if($delete){
            return response()->json(['message' => 'User successfully Log out'], 200);
        }else{
            return response()->json(['message' => 'Token Not Found'],404);
        }


    }

    public function showuser()
    {

    }



}
