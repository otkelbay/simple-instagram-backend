<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $guarded = ['id'];

    protected $appends = ['liked', 'likes'];

    public function author()
    {
        return $this->belongsTo('App\User', 'author_id');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }

    public static function decodeBase64($data)
    {
        $data = explode(',', $data);
        $data = count($data) > 1 ? $data[1] : $data[0];
        $data = base64_decode($data);
        return $data;
    }

    public static function getFileUrl(Request $request)
    {
        $photoName = explode('.', $request->get('photo_name'));
        $type = end($photoName);
        $fileName = Str::random() . ".$type";
        $data = $request->get('photo_content');
        $filePath = public_path("handled_files/$fileName");
        $data = static::decodeBase64($data);
        $fullPath = file_put_contents("$filePath", $data, FILE_APPEND | LOCK_EX)
            ? "/handled_files/$fileName"
            : false;

        return $fullPath;
    }

    public function getPhotoUrlAttribute($value)
    {
        if (strpos($value,'http') === 0){
            return $value;
        }
        return config('app.url') . $value;
    }

    public function getLikedAttribute()
    {
        $like = DB::table('user_post_likes')
            ->where('post_id', $this->id)
            ->where('user_id', Auth::id())
            ->first();

        return !!$like;
    }

    public function getLikesAttribute()
    {
        return DB::table('user_post_likes')
            ->where('post_id', $this->id)
            ->count();
    }
}
