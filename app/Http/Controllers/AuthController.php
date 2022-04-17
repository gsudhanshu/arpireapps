<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * register a new user
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:55',
            'role_id' => 'required|numeric',
            'email' => 'email|required|unique:users',
            'password' => 'required|confirmed'
        ]);

        //$validatedData['password'] = bcrypt($request->password);
        $validatedData['password'] = Hash::make($request->password);
        //$validatedData['status'] = 'active';

        $user = User::create($validatedData);

        $accessToken = $user->createToken('authToken')->accessToken;

        $role = Role::findOrFail($user->role_id);
        return response([ 'user' => $user, 'role' => $role, 'access_token' => $accessToken]);
    }

    /**
     * Login using given user credentials
     */
    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        $loginData['email'] = strtolower($loginData['email']);
        
        if (!auth()->attempt($loginData)) {
            return response(['message' => 'Invalid Credentials'], 401);
        }

        if (auth()->user()->status == 0) {
            return response(['message' => 'User status is InActive'], 401);
        }

        $user = auth()->user();
        $accessToken = $user->createToken('authToken')->accessToken;
        $role = Role::findOrFail($user->role_id);
        
        return response([ 'user' => $user, 'role' => $role, 'access_token' => $accessToken]);
    }

    public function logout(Request $request) 
    {
        if(Auth::check()) {
            $request->user()->token()->revoke();
            return response()->json(["message" => "User logged out"], 200);
        } else {
            return response()->json(["message" => "Invalid request"], 400);
        }
    }
}
