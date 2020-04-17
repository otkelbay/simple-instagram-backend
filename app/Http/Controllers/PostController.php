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
        $type = end(explode('.', $request->get('photo_name')));
        $fileName = Str::random() . ".$type";
        $data = $request->get('photo_content');
        $filePath = public_path("handled_files") . '/' . $fileName;
        $data = explode(',', $data);
        $data = count($data) > 1 ? $data[1] : $data[0];
        $data = base64_decode($data);
        $fullPath = file_put_contents("$filePath", $data, FILE_APPEND | LOCK_EX)
            ? config('app.url') . "/$filePath"
            : false;
        return $fullPath;
    }

    public function getFeed()
    {
        /* @var $user User */
        $user = Auth::user();
        $subscribersIds = $user->subscribes()->pluck('id');
        $posts = Post::whereIn('author_id', $subscribersIds)->orderBy('id', 'DESC')->simplePaginate(20);
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
            $like->delete();
        }
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
    }
}
