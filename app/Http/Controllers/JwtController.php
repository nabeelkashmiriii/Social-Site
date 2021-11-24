<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtController extends Controller
{
    public static function jwt_encode($data)
    {
        $key = "example_key";
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
        $secret_key = "example_key";

        try {

            $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['message'] = "Someting went Worng";
            return response()->error($data, 404);
        }
    }
}
