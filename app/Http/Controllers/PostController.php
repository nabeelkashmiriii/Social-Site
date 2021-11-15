<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    //
    public function post(Request $request)
    {

        $jwt = $request->bearerToken();
        if (User::where("jwt_token", $jwt)->exists()) {
            $key = "example_key";
            $decode = JWT::decode($jwt, new key($key, 'HS256'));

            
            $validator = Validator::make($request->all(), [
                'body' => 'string|max:1000',
                'file' => 'required|mimes:jpg,png,docx,txt,mp4,pdf,ppt|max:10000',
                'privacy' => 'required|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }
            $post = new Post;
            $post->user_id = $decode->data->id;
            $post->body = $request->body;
            $post->privacy = $request->privacy;

            $fileName = time() . '_' . $request->file->getClientOriginalName();
            $filePath = $request->file('file')->storeAs('uploads', $fileName, 'public');
            $post->file = '/storage/' . $filePath;
            $result = $post->save();
            if ($result) {
                return response()->json(['message' => 'Your Content has been Posted'], 201);
            } else {
                return response()->json(['message' => 'Failed to Post'], 400);
            }
        } else {
            return response()->json(['message' => 'UnAuthorized User'], 401);
        }
    }


    // Delete Post
    public function deletPost(Request $request)
    {
        $post_id = $request->id;
        $jwt = $request->bearerToken();
        if (User::where("jwt_token", $jwt)->exists()) {
            $key = "example_key";
            $decode = JWT::decode($jwt, new key($key, 'HS256'));
            $user_id = $decode->data->id;
            $matchthese = ['id' => $post_id, 'user_id' => $user_id];
            $delet = Post::where($matchthese)->delete();

            if ($delet) {

                return response()->json(['message' => 'deleted'], 201);
            } else {
                return response()->json(['message' => ' not deleted'], 404);
            }
        } else {
            return response()->json(['message' => 'UnAuthorized User'], 401);
        }
    }

    // update post
    public function searchPost(Request $request)
    {
        $post_id = $request->id;
        $jwt = $request->bearerToken();
        if (User::where("jwt_token", $jwt)->exists()) {
            $key = "example_key";
            $decode = JWT::decode($jwt, new key($key, 'HS256'));
            $user_id = $decode->data->id;
            $matchthese = ['id' => $post_id, 'user_id' => $user_id];


            if ($matchthese) {

                return response()->json([$search = Post::where($matchthese)->get(),], 201);
            } else {
                return response()->json(['message' => ' not found'], 404);
            }
        } else {
            return response()->json(['message' => 'UnAuthorized User'], 401);
        }
    }
}
