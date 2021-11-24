<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRegisterRequest;
use MongoDB\Client as DB;
use App\Http\Controllers\JwtController;

class UserController extends Controller
{
    //User Registration
    public function register(UserRegisterRequest $request)
    {
        $validator = $request->validated();
        $validator["password"] = bcrypt($validator["password"]);

        $db = (new DB)->SocialSite->users;
        $email_exist = $db->findOne(['email' => $request->email]);
        if (!$email_exist) {
            $user = $db->insertOne([
                "name" => $validator["name"],
                "email" => $validator["email"],
                "password" => $validator["password"]
                // "verify"=>0
            ]);
        } else {
            return response()->error(['message' => 'User Already Exist'], 400);
        }



        UserController::sendEmail($request->name, $request->email);


        return response()->success([
            'message' => 'User successfully registered',
            'user' => $user,
        ], 201);
    }


    // Send Email
    public static function sendEmail($name, $email)
    {
        $user = [
            'name' => $name,
            'info' => 'Press the Following Link to Verify Email',
            'Verification_link' => url('user/verifyEmail/' . $email)
        ];
        \Mail::to($email)->send(new \App\Mail\NewMail($user));
    }


    // verification
    public function verify($email)
    {
        $db = (new DB)->SocialSite->users;
        $data = $db->findOne(['email' => $email]);
        // dd($data['verify']);
        if (isset($data['verify'])) {
            return response()->success(['message' => 'Your account has been verified'], 200);
        } else {
            $update = $db->updateOne(['email' => $email], ['$set' => ['verify' => 1]]);
            if ($update) {
                return "Your Account has beem verified";
            } else {
                return response()->error(['message' => 'Email Not verified verified'], 400);
            }
        }
    }


    // User Login
    public function login(Request $request)
    {

        $db = (new DB)->SocialSite->users;
        if ($data = $db->findOne(['email' => $request->email])) {

            $user_data = array(
                "id" => (string)$data->_id,
                "name" => $data->name,
                "email" => $data->email
            );
            // check condition for verified email
            if (isset($data['verify'])) {

                $jwt = (new JwtController)->jwt_encode($user_data);
                $db->updateOne(['email' => $request->email], ['$set' => ['jwt_token' => $jwt]]);

                return response()->success([
                    'message' => 'User successfully Loged In',
                    'user' => $user_data,
                    'token' => $jwt,
                ], 200);
            } else {
                // verify($user->email);
                // call Method to send email Verification
                UserController::sendEmail($user_data['name'], $user_data['email']);

                return response()->error([
                    'message' => 'User email not verified Please Check Your email to verify',
                    // 'user' => $user
                ], 400);
            }
        } else {
            return response()->error([
                'message' => 'User Not Found',

            ], 404);
        }
    }
    // logout

    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        // dd($token);
        $db = (new DB)->SocialSite->users;
        $delete = $db->updateOne(['jwt_token' => $token], ['$unset' => ['jwt_token' => null]]);
        if ($delete) {
            return response()->success(['message' => 'User successfully Log out'], 200);
        } else {
            return response()->error(['message' => 'Token Not Found'], 404);
        }
    }
}
