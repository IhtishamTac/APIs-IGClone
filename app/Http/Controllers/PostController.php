<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Models\User;
use App\Models\PostAttachment;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:0',
            'size' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()
            ], 422);
        }
        $pages = $request->page;
        $sizes = $request->size;
        $posts = Post::with('user','attachments')->get();
        $posts->makeHidden('user_id');

        // if($pages || $sizes == null){
        //     $pages = 0;
        //     $sizes = 10;

        //     return response()->json([
        //         'page' => $pages,
        //         'size' => $sizes,
        //         'posts' => $posts
        //     ]);
        // }

        return response()->json([
            'page' => $pages,
            'size' => $sizes,
            'posts' => $posts
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'caption' => 'required',
            'attachments.*' => 'required|mimes:jpg,jpeg,webp,png,gif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $post = new Post();
        $post->caption = $request->caption;
        $post->user_id = auth()->id();
        $post->save();

        if ($post) {
            foreach ($request->attachments as $att) {
                $imageName = time() . '.' . $att->getClientOriginalName();
                $att->move(public_path('post_attach'), $imageName);

                $poatt = new PostAttachment();
                $poatt->storage_path = 'post_attach/' . $imageName;
                $poatt->post_id = $post->id;
                $poatt->save();

                if($poatt){
                    return response()->json([
                        'message' => 'Create post success'
                    ], 200);
                }
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::where('id', $id)->first();

        if($post){
            $user = User::where('id', auth()->id())->first();
            if($post->user_id != $user->id){
                return response()->json([
                    'message' => 'Forbidden access'
                ], 403);
            }
            // $attachments = $post->attachments;
            if($post->delete()){
                return response()->json([
                ], 204);
            }
        }

        return response()->json([
            'message' => 'Post not found'
        ], 404);
    }
}
