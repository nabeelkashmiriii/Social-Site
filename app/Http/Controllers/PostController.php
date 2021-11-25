<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use App\Http\Controllers\JwtController;
use App\Service\MongodbConn;

class PostController extends Controller
{
    //Create Post
    public function post(PostRequest $request)
    {

        $jwt = $request->bearerToken();
        $decode = JwtController::jwt_decode($jwt);

        $validator = $request->validated();

        // Get Connection
        $connection = new MongodbConn('posts');
        $db = $connection->getConnection();
        $fileName = time() . '_' . $validator["file"]->getClientOriginalName();
        $filePath = $validator["file"]->storeAs('uploads', $fileName, 'public');
        $post_save = $db->insertOne([
            'file' => '/storage/' . $filePath,
            'user_id' => $decode->data->id,
            'body' => $validator["body"],
            'privacy' => $validator["privacy"],
            //'comments' => array(null)
        ]);

        if ($post_save) {
            return response()->success(['message' => 'Your Content has been Posted'], 201);
        } else {
            return response()->error(['message' => 'Failed to Post'], 400);
        }
    }


    // Delete Post
    public function deletPost(Request $request)
    {
        // Get Connection
        $connection = new MongodbConn('posts');
        $db = $connection->getConnection();

        $post_id = $request->_id;




        $delet = $db->deleteOne(["_id" => new \MongoDB\BSON\ObjectId($post_id)]);

        if ($delet) {

            return response()->success(['message' => 'deleted'], 201);
        } else {
            return response()->error(['message' => ' not deleted'], 404);
        }
    }

    // update post
    public function searchPost(Request $request)
    {
        // Get Connection
        $connection = new MongodbConn('posts');
        $db = $connection->getConnection();
        $post_id = $request->id;
        $search = $db->findOne(["_id" => new \MongoDB\BSON\ObjectId($post_id)]);


        if ($search) {

            return response()->success([$search], 201);
        } else {
            return response()->error(['message' => ' not found'], 404);
        }
    }
}
