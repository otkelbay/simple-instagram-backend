<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/* @property string $login
 * @property string $password
 * @property string $api_token
 * */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function posts()
    {
        return $this->hasMany('App\Post', 'author_id');
    }

    public function subscribes()
    {
        return $this->belongsToMany('App\User', 'user_subscribers', 'user_id','subscribed_to_id');
    }

    public function followers()
    {
        return $this->belongsToMany('App\User', 'user_subscribers', 'subscribed_to_id');
    }

    public function getSubCountAttribute()
    {
        return $this->subscribes()->count();
    }

    public function getFollowersCountAttribute()
    {
        return $this->followers()->count();
    }


}
