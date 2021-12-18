<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate(
            [
                'name' => 'required|string',
                'email' => 'required|string|unique:users,email',
                'password' => 'required|string|confirmed'
            ]
        );
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),

        ]);
        $token = $user->createToken('myAppToken')->plainTextToken;
        $response = [
            'user' => $user,
            'token' => $token
        ];
        return response($response, '200');
    }

    public function login(Request $request)
    {
        $fields = $request->validate(
            [
                'email' => 'required|string',
                'password' => 'required|string|'
            ]
        );

        $user = User::where('email', 'like', $fields['email'])->first();

        if ($user && Hash::check($fields['password'], $user->password)) {
            $token = $user->createToken('myAppToken')->plainTextToken;
            $response = [
                'user' => $user,
                'token' => $token
            ];
            return response($response, '200');
        } else {
            $response = [
                'error' => 'user not found'
            ];
            return response($response, '401');
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(),  [
            'password' => 'required|string|confirmed'
        ]);
        if ($validator->fails()) {
          
            foreach ($validator->messages()->getMessages() as $field_name => $messages) {
                return ['error' => $messages];
            }
        }

    
        auth()->user()->password =  bcrypt($request['password']);
        auth()->user()->save();
        $response =  ([
            'msg' =>'password changed',
        ]);

        return response($response,'200');
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        $response =  [
            'message' => 'logged out'
        ];
        return response($response, '200');
    }
}
