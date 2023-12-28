<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Follow;

class UserController extends Controller
{
    public function index(){
        $follow = Follow::where('follower_id', auth()->id())->get();
        $followedUserId = $follow->pluck('following_id');
        $notFollowedUser = User::whereNotIn('id', $followedUserId)->where('id', '!=', auth()->id() )->get();
        return response()->json([
            'users' => $notFollowedUser
        ], 200);
    }

    public function getDetailUser(string $username){
        $user = User::where('username', $username)->first();
        $postDatas = [];
        if($user){
            if($user->id == auth()->id()){
                $user->is_your_account = true;
            }else if($user->id != auth()->id()){
                $user->is_your_account = false;
            }
            $followRequested = Follow::where('follower_id', auth()->id())
                    ->where('following_id', $user->id)
                    ->first();

            if (!$followRequested) {
                $user->following_status = 'not-following';
            } else {
                $isAccepted = $followRequested->is_accepted;

                if ($isAccepted === null) {
                    $user->following_status = 'requested';
                } else if ($isAccepted) {
                    $user->following_status = 'following';
                } else {
                    $user->following_status = 'not-following';
                }
            }
            $posts = Post::where('user_id', $user->id)->with('attachments')->get();
            $postsCount = Post::where('user_id', $user->id)->count();
            $followersCount = Follow::where('following_id', $user->id)->count();
            $followingsCount = Follow::where('follower_id', $user->id)->count();
            $user->posts_count = $postsCount;
            $user->followers_count = $followersCount;
            $user->following_count = $followingsCount;
            foreach ($posts as $post) {
                $postDatas[] = $post;
            }
            $user->posts = $postDatas;
            return response()->json([
                'user' => $user
            ], 200);
        }
        return response()->json([
            'message' => 'User not found'
        ], 404);
    }
}
