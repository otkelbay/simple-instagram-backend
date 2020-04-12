<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only(['login', 'password']))) {
            $user = Auth::user();
            return response()->json($user);
        }
        return response()->json([
            'error' => 'credentials are wrong'
        ], 403);
    }

    public function register(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required|confirmed'
        ]);

        $user = User::where('login', $request->get('login'))->first();

        if ($user) {
            if (Auth::attempt($request->only(['login', 'password']))) {
                $user = Auth::user();
                return response()->json($user);
            }
            return response()->json([
                'error' => 'user with this login already exists'
            ], 402);
        }
        $user = new User();
        $user->login = $request->get('login');
        $user->password = Hash::make($request->get('password'));
        $user->api_token = Str::random(80);
        $user->save();
        return response()->json($user);
    }
}
