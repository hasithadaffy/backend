<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Post;
use App\Models\Images;
use DB;
use Validator;

class PostController extends Controller
{
    //

    public function __construct(){
        $this->middleware("auth:api");
        $this->post = new Post;
        $this->img = new Images;
    }

    public function create_post(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
            'title' => 'required',
            'description' => 'required',
            'image' => 'required',
            'image.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if($validator->fails()) {
            return response()->json(["status" => "failed", "message" => "Validation error", "errors" => $validator->errors()]);
        }

        $post = new Post();
        $post->title = $request->title;
        $post->description = $request->description;

        if(auth()->user()->posts()->save($post))
        {

            $data = Post::latest('id')->first();
            $post_id = $data->id;

            $name = $request->file('image')->getClientOriginalName();
            $path = $request->file('image')->store('public/images');


            Images::create([
                'image_url' => $name,
                'post_id' => $post_id
            ]);

            return response()->json([
                'success' => true,
                'data' => $post->toArray(),
            ]);


        }else{
            return response()->json([
                'success' => false,
                'message' => 'Post not added'
            ], 500);
        }
    }

    public function post_list()
    {
        $posts = Post::all();

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }
}
