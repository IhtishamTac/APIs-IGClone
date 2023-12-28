<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'full_name' => 'required',
            'bio' => 'required|max:100',
            'username' => 'required|min:3|unique:users,username|regex:/^[a-zA-Z0-9._]+$/',
            'password' => 'required|min:6',
            'is_private' => 'boolean'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $sus = User::create([
            'full_name' => $request->full_name,
            'bio' => $request->bio,
            'username' => $request->username,
            'password' => $request->password,
            'is_private' => $request->is_private
        ]);

        $credentials = $request->only(['username', 'password']);
        if(!Auth::attempt($credentials)){
            return response()->json([
                'message' => 'Username or password incorrect'
            ], 403);
        }

        $user = User::where('username', $request->username)->first();
        $token = $user->createToken('TOKEN SANCTUM')->plainTextToken;

        return response()->json(
            [
                'message' => 'Register success',
                'token' => $token,
                'user' => $sus
            ], 200);

    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'username' => 'required',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only(['username', 'password']);
        if(!Auth::attempt($credentials)){
            return response()->json([
                'message' => 'Username or password incorrect'
            ], 403);
        }

        $user = User::where('username', $request->username)->first();
        $token = $user->createToken('TOKEN SANCTUM')->plainTextToken;

        return response()->json(
            [
                'message' => 'Login success',
                'token' => $token,
                'user' => $user
            ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logout success'
        ], 200);
    }
}
