<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    //
    public function comment(Request $request)
    {
        $jwt = $request->bearerToken();
        $key = "example_key";
        $decode = JWT::decode($jwt, new key($key, 'HS256'));

        $validator = Validator::make($request->all(), [
            'body' => 'string|max:1000',
            'file' => 'required|mimes:jpg,png,docx,txt,mp4,pdf,ppt|max:10000',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $comment = new Comment;
        $comment->post_id = $request->id;
        $comment->user_id = $decode->data->id;
        $comment->user_name = $decode->data->name;
        $comment->body = $request->body;

        $fileName = time() . '_' . $request->file->getClientOriginalName();
        $filePath = $request->file('file')->storeAs('uploads', $fileName, 'public');
        $comment->file = '/storage/' . $filePath;
        $result = $comment->save();
        if ($result) {
            return response()->json(['message' => 'Your Comment has been Posted'], 201);
        } else {
            return response()->json(['message' => 'Failed to Comment'], 400);
        }
    }

    // delete comment
    public function deleteComment(Request $request)
    {
        $comment_id = $request->id;
        $jwt = $request->bearerToken();

        $key = "example_key";
        $decode = JWT::decode($jwt, new key($key, 'HS256'));
        $user_id = $decode->data->id;
        $matchthese = ['id' => $comment_id, 'user_id' => $user_id];
        $delet = Post::where($matchthese)->delete();

        if ($delet) {

            return response()->json(['message' => 'deleted'], 201);
        } else {
            return response()->json(['message' => ' not deleted'], 404);
        }
    }
}
