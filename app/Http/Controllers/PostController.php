<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use MongoDB\Client as DB;
use App\Http\Controllers\JwtController;

class PostController extends Controller
{
    //Create Post
    public function post(PostRequest $request)
    {

        $jwt = $request->bearerToken();
        $decode = JwtController::jwt_decode($jwt);

        $validator = $request->validated();

        $db = (new DB)->SocialSite->posts;
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
        $db = (new DB)->SocialSite->posts;

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
        $db = (new DB)->SocialSite->posts;
        $post_id = $request->id;
        $search = $db->findOne(["_id" => new \MongoDB\BSON\ObjectId($post_id)]);


        if ($search) {

            return response()->success([$search], 201);
        } else {
            return response()->error(['message' => ' not found'], 404);
        }
    }
}
