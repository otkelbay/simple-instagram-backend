<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Post;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, File};
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function uploadPost(Request $request)
    {
        $request->validate([
            'text' => 'string',
            'photo_content' => 'required',
            'photo_name' => 'required'
        ]);

        $fileUrl = Post::getFileUrl($request);
        $post = new Post(
            [
                'photo_url' => $fileUrl,
                'post_text' => $request->get('text'),
                'author_id' => Auth::id()
            ]
        );
        $post->save();
        return response()->json($post);
    }

    public function getFeed()
    {
        /* @var $user User */
        $user = Auth::user();
        $subscribersIds = $user->subscribes()->pluck('user_subscribers.subscribed_to_id');
        $posts = Post::whereIn('author_id', $subscribersIds)->with('author:avatar,login,id')->inRandomOrder("'".strval(rand(1,10000))."'")->simplePaginate(20);
        return response()->json($posts);
    }

    public function like(Request $request)
    {
        $request->validate([
            'post_id' => 'required',
            'like' => 'required'
        ]);

        $user = Auth::user();

        $like = DB::table('user_post_likes')
            ->where('post_id', $request->get('post_id'))
            ->where('user_id', $user->id)
            ->first();
        if (!$like and $request->get('like')) {
            DB::table('user_post_likes')
                ->insert([
                    'user_id' => $user->id,
                    'post_id' => $request->get('post_id')
                ]);
        } elseif ($like and !$request->get('like')) {
            DB::table('user_post_likes')
                ->where('post_id', $request->get('post_id'))
                ->where('user_id', $user->id)
                ->delete();
        }

        return response()->json([
            'ok' => true
        ]);
    }

    public function comment(Request $request)
    {
        $request->validate([
            'post_id' => 'required',
            'text' => 'required|string',
            'reply_to' => 'integer|exists:comments,id',
        ]);

        $user = Auth::user();

        $comment = new Comment(
            [
                'post_id' => $request->get('post_id'),
                'user_id' => $user->id,
                'text' => $request->get('text')
            ]
        );
        if ($request->get('reply_to')) {
            $comment->reply_to = $request->get('reply_to');
        }
        $comment->save();

        return response()->json(Comment::with('user')->find($comment->id));
    }

    public function getPost($id)
    {
        $post = Post::with('comments.user:avatar,login,id','author:avatar,login,id')->find($id);
        return response()->json($post);
    }
}
