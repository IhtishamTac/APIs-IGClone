<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Follow;
use App\Models\User;

class FollowController extends Controller
{
    public function follAUser(string $username){
        $user = auth()->user();

        $targetUser = User::where('username', $username)->first();

        if ($targetUser) {
            if ($targetUser->id === $user->id) {
                return response()->json([
                    'message' => 'You are not allowed to follow yourself'
                ], 422);
            }

            $follow = Follow::where('follower_id', $user->id)->where('following_id', $targetUser->id)->first();

            $isAccepted = $follow->is_accepted;
            if ($follow) {

                if (!$isAccepted) {
                    return response()->json([
                        'message' => 'You are already followed',
                        'status' => 'requested'
                    ]);
                } else {
                    return response()->json([
                        'message' => 'You are already followed',
                        'status' => 'following'
                    ]);
                }
            }
            $newFollow = new Follow();
            $newFollow->follower_id = $user->id;
            $newFollow->following_id = $targetUser->id;
            $newFollow->save();

            $followStatus = $isAccepted ? 'following' : 'requested';

            return response()->json([
                'message' => 'Follow success',
                'status' => $followStatus
            ], 200);
        }
        return response()->json([
            'message' => 'User not found'
        ], 404);
    }

    public function unfollAUser(string $username){
        $user = User::where('username', $username)->first();
        if($user){
            $follow = Follow::where('follower_id', auth()->id())->where('following_id', $user->id)->first();
            if($follow){
                $follow->delete();
                return response()->json([
                ], 204);
            }
            return response()->json([
                'message' => 'You are not following the user'
            ], 422);
        }
        return response()->json([
            'message' => 'User not found'
        ], 404);
    }

    public function getFollUser(){
        $foll = Follow::where('follower_id', auth()->id())->get();
        if($foll){
            $follIds = $foll->pluck('following_id');
            $user = User::whereIn('id', $follIds)->get();
            return response()->json([
                'following' => $user
            ], 200);
        }
        return response()->json([
            'message' => 'User not found'
        ], 200);
    }

    public function accFollUser(string $username){
        $user = User::where('username', $username)->first();
        if($user){
            $follow = Follow::where('following_id', auth()->id())->where('follower_id', $user->id)->first();
            if($follow){
                if($follow->is_accepted === null){
                    $follow->is_accepted = true;
                    $follow->save();
                    return response()->json([
                        'message' => 'Follow request accepted'
                    ], 200);
                }
                return response()->json([
                    'message' => 'Follow request is already accepted'
                ], 422);
            }
            return response()->json([
                'message' => 'The user is not following you'
            ], 422);
        }
        return response()->json([
            'message' => 'User not found'
        ], 404);
    }

    public function getFollowersUser(string $username){
        $users = User::where('username', $username)->first();
        $usrId = $users->id;
        $foll = Follow::where('following_id', $usrId)->where('is_accepted', true)->get();
        if($foll){
            $follIds = $foll->pluck('follower_id');
            $user = User::whereIn('id', $follIds)->get();
            return response()->json([
                'followers' => $user
            ], 200);
        }
        return response()->json([
            'message' => 'User not found'
        ], 200);
    }
}
