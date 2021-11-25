<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use Exception;
use App\Http\Requests\PostRequest;
use App\Service\JwtAuthentication;

class PostController extends Controller
{
    //
    public function post(PostRequest $request)
    {
        try {

            $token = $request->bearerToken();
            if (User::where("jwt_token", $token)->exists()) {

                $jwt = new JwtAuthentication;
                $decode = $jwt->jwt_decode($token);
                // dd($decode);
                $post = new Post;
                $post->user_id = $decode->data->id;
                $post->body = $request->body;
                $post->privacy = $request->privacy;

                $fileName = time() . '_' . $request->file->getClientOriginalName();
                $filePath = $request->file('file')->storeAs('uploads', $fileName, 'public');
                $post->file = '/storage/' . $filePath;
                $result = $post->save();
                if ($result) {
                    return response()->success(['message' => 'Your Content has been Posted'], 201);
                } else {
                    return response()->error(['message' => 'Failed to Post'], 400);
                }
            } else {
                return response()->error(['message' => 'UnAuthorized User'], 401);
            }
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 401);
        }
    }


    // Delete Post
    public function deletPost(Request $request)
    {
        try {
            $post_id = $request->id;
            $token = $request->bearerToken();
            if (User::where("jwt_token", $token)->exists()) {
                $jwt = new JwtAuthentication;
                $decode = $jwt->jwt_decode($token);
                $user_id = $decode->data->id;
                $matchthese = ['id' => $post_id, 'user_id' => $user_id];
                $delet = Post::where($matchthese)->delete();

                if ($delet) {

                    return response()->success(['message' => 'deleted'], 201);
                } else {
                    return response()->error(['message' => ' not deleted'], 404);
                }
            } else {
                return response()->error(['message' => 'UnAuthorized User'], 401);
            }
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 401);
        }
    }

    // update post
    public function searchPost(Request $request)
    {
        try {
            $post_id = $request->id;
            $token = $request->bearerToken();
            if (User::where("jwt_token", $token)->exists()) {
                $jwt = new JwtAuthentication;
                $decode = $jwt->jwt_decode($token);
                $user_id = $decode->data->id;
                $matchthese = ['id' => $post_id, 'user_id' => $user_id];


                if ($matchthese) {

                    return response()->success([Post::where($matchthese)->get(),], 201);
                } else {
                    return response()->error(['message' => ' not found'], 404);
                }
            } else {
                return response()->error(['message' => 'UnAuthorized User'], 401);
            }
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 401);
        }
    }
}
