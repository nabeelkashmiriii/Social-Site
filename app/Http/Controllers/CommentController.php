<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Requests\CommentRequest;
use App\Service\MongodbConn;


class CommentController extends Controller
{
    //
    public function comment(CommentRequest $request)
    {
        $jwt = $request->bearerToken();
        $decode = JwtController::jwt_decode($jwt);


        $validator = $request->validated();

        $fileName = time() . '_' . $request->file->getClientOriginalName();
        $filePath = $request->file('file')->storeAs('uploads', $fileName, 'public');
        $post_id = new \MongoDB\BSON\ObjectId($request->post_id);

        $comment = array(

            '_id' => new \MongoDB\BSON\ObjectId(),

            'user_id' => $decode->data->id,

            'file' => $filePath,

            'body' => $validator['body']
        );
        // Get Connection
        $connection = new MongodbConn('posts');
        $db = $connection->getConnection();
        $result = $db->updateOne(["_id" => $post_id], ['$push' => ["comments" => $comment]]);
        if ($result) {
            return response()->success(['message' => 'Your Comment has been Posted'], 201);
        } else {
            return response()->error(['message' => 'Failed to Comment'], 400);
        }
    }

    // delete comment
    public function deleteComment(Request $request)
    {
        $comment_id = $request->id;

        // Get Connection
        $connection = new MongodbConn('posts');
        $db = $connection->getConnection();
        $delet = $db->deleteOne(["_id" => new \MongoDB\BSON\ObjectId($comment_id)]);


        if ($delet) {

            return response()->success(['message' => 'deleted'], 201);
        } else {
            return response()->error(['message' => ' not deleted'], 404);
        }
    }
}
