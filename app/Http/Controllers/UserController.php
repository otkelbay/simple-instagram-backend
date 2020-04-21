<?php

namespace App\Http\Controllers;

use App\Post;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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
        $user->avatar = 'https://api.adorable.io/avatars/285/' . rand(1,100000);
        $user->password = Hash::make($request->get('password'));
        $user->api_token = Str::random(80);
        $user->save();
        return response()->json($user);
    }

    public function getSuggested()
    {
        $users = User::where('id', '!=', Auth::id())->inRandomOrder()->limit(20)->get();
        return response()->json($users);
    }

    public function getUser()
    {
        return response()->json(Auth::user());
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'subscribe_to' => 'required|exists:users,id',
            'follow' => 'required'
        ]);

        $user = Auth::user();

        $follow = DB::table('user_subscribers')
            ->where('subscribed_to_id', $request->get('subscribe_to'))
            ->where('user_id', $user->id)
            ->first();
        if (!$follow and $request->get('follow')) {
            DB::table('user_subscribers')
                ->insert([
                    'user_id' => $user->id,
                    'subscribed_to_id' => $request->get('subscribe_to')
                ]);
        } elseif ($follow and !$request->get('follow')) {
            DB::table('user_subscribers')
                ->where('subscribed_to_id', $request->get('subscribe_to'))
                ->where('user_id', $user->id)
                ->delete();
        }

        return response()->json([
            'ok' => true
        ]);
    }

    public function userPosts(Request $request)
    {
        $request->validate([
            'user_id' => 'exists:users,id'
        ]);

        $userId = $request->get('user_id');
        $userId = $userId ? $userId : Auth::id();

        $user = User::with('posts','subscribes','followers')->find($userId);
        $user->following = User::isFollowing($userId);

        return response()->json($user);
    }
}
