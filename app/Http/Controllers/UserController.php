<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Controllers\JwtController;
use App\Service\MongodbConn;
use Exception;
use Illuminate\Support\Facades\hash;


class UserController extends Controller
{
    //User Registration
    public function register(UserRegisterRequest $request)
    {
        try {
            $validator = $request->validated();
            $validator["password"] = bcrypt($validator["password"]);

            // get Connection
            $connection = new MongodbConn('users');
            $db = $connection->getConnection();
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
            ], 201);
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 400);
        }
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
        try {
            $connection = new MongodbConn('users');
            $db = $connection->getConnection();
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
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 400);
        }
    }


    // User Login
    public function login(Request $request)
    {
        try {
            //get Connection
            $connection = new MongodbConn('users');
            $db = $connection->getConnection();
            $data = $db->findOne(['email' => $request->email]);
            if(Hash::check($request->password, $data->password)) {

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

                    UserController::sendEmail($user_data['name'], $user_data['email']);

                    return response()->error([
                        'message' => 'User email not verified Please Check Your email to verify',

                    ], 400);
                }
            } else {
                return response()->error([
                    'message' => 'User Not Found',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->error($e, 404);
        }
    }
    // logout

    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();
            // dd($token);

            //get Connection
            $connection = new MongodbConn('users');
            $db = $connection->getConnection();
            $delete = $db->updateOne(['jwt_token' => $token], ['$unset' => ['jwt_token' => null]]);
            if ($delete) {
                return response()->success(['message' => 'User successfully Log out'], 200);
            } else {
                return response()->error(['message' => 'Token Not Found'], 404);
            }
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 400);
        }
    }
}
