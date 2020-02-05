<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request){

        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'email|required',
            'password' => 'required'
        ]);

        $user = User::create($validatedData);
        $accessToken = $user->createToken('authToken')->accessToken;
        return response(['user'=> $user,'access_token'=>$accessToken]);
        
    }


    public function login(Request $request){

        $loginData = $request->all();
        if(!auth()->attempt($loginData)){
            return response(['message'=>'Invalid credentials']);
        }
        $user = auth()->user();
        $accessToken = $user->createToken('authToken')->accessToken;
        return response(['user'=> $user,'access_token'=>$accessToken]);
    }
}
