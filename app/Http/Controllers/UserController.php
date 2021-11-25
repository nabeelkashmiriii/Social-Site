<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Service\JwtAuthentication;
use Exception;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    //User Registration
    public function register(UserRequest $request)
    {
        try {
            $user = User::create(array_merge(
                $request->all(),
                ['password' => bcrypt($request->password)]
            ));

            // dd($user);
            UserController::sendEmail($request->name, $request->email);

            return response()->success([
                'message' => 'User successfully registered',
                'user' => $user
            ], 201);
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 401);
        }
    }
    // Send Email
    public static function sendEmail($name, $email)
    {
        $user = [
            'name' => $name,
            'info' => 'Press the Following Link to Verify Email',
            'Verification_link' => url('api/verifyEmail/' . $email)
        ];
        \Mail::to($email)->send(new \App\Mail\NewMail($user));
    }


    // verification
    public function verify($email)
    {
        if (User::where("email", $email)->value('verify') == 1) {
            return response()->success(['message' => 'Your account has been verified'], 200);
        } else {
            $update = User::where("email", $email)->update(["verify" => 1, "email_verified_at" => date('Y-m-d H:i:s')]);
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
try{
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

            $user = Auth::user();
            $user_data = array(
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email
            );
            // check condition for verified email
            if (User::where("email", $user->email)->value('verify') == 1) {
                $jwt = new JwtAuthentication;
                $token = $jwt->jwt_encode($user_data);

                User::where("email", $user->email)->update(["jwt_token" => $token]);

                return response()->success([
                    'message' => 'User successfully Loged In',
                    // 'user' => $user,
                    'token'=> $token
                ], 200);
            } else {
                return response()->error([
                    'message' => 'User email not verified Please Check Your email to verify',

                ], 400);

                // verify($user->email);

                // call Method to send email Verification
                UserController::sendEmail($user->name, $user->email);
            }
        } else {
            return response()->error([
                'message' => 'User Not Found',

            ], 404);
        }
    }
    catch (Exception $e) {
        return response()->error($e->getMessage(), 401);
    }
    }
    // logout

    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        // dd($token);

        $delete = User::where("jwt_token", $token)->update(["jwt_token" => NULL]);
        if ($delete) {
            return response()->success(['message' => 'User successfully Log out'], 200);
        } else {
            return response()->error(['message' => 'Token Not Found'], 404);
        }
    }
    public function resource(Request $request)
    {
        $token = $request->bearerToken();
        $jwt = new JwtAuthentication;
        $decode = $jwt->jwt_decode($token);

        $user = User::find($decode->data->id);

        return new UserResource($user);
    }
}
