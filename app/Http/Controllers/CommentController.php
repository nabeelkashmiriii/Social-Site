<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use App\Http\Requests\CommentRequest;
use App\Service\JwtAuthentication;
use Exception;


class CommentController extends Controller
{
    //
    public function comment(CommentRequest $request)
    {
        try {
            $token = $request->bearerToken();
            $jwt = new JwtAuthentication;
            $decode = $jwt->jwt_decode($token);

            $comment = new Comment;
            $comment->post_id = $request->post_id;
            $comment->user_id = $decode->data->id;
            $comment->user_name = $decode->data->name;
            $comment->body = $request->body;

            $fileName = time() . '_' . $request->file->getClientOriginalName();
            $filePath = $request->file('file')->storeAs('uploads', $fileName, 'public');
            $comment->file = '/storage/' . $filePath;
            $result = $comment->save();
            if ($result) {
                return response()->success(['message' => 'Your Comment has been Posted'], 201);
            } else {
                return response()->error(['message' => 'Failed to Comment'], 400);
            }
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 401);
        }
    }

    // delete comment
    public function deleteComment(Request $request)
    {
        $comment_id = $request->id;
        $token = $request->bearerToken();

        $jwt = new JwtAuthentication;
        $decode = $jwt->jwt_decode($token);
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
