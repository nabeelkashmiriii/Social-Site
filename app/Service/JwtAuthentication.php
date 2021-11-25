<?php
namespace App\Service;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtAuthentication{
    public static function jwt_encode($data)
    {
        $key = config('constant.secret');
        // dd($key);
        $payload = array(
            "iss" => "http://localhost.com",
            "aud" => "http://localhost.com",
            "iat" => time(),
            "exp" => time() + 3600,
            "data" => $data
        );
        try {
            $token = JWT::encode($payload, $key, 'HS256');
            return $token;
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }


    public static function jwt_decode($jwt)
    {


        try {
            $key = config('constant.secret');
            $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['message'] = "Someting went Worng";
            return response()->error($data, 404);
        }
    }
}
