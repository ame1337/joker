<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected static function boot()
    {
        parent::boot();

        static::created(function($user) {
            Player::create(['user_id' => $user->id]);
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'email_verified_at',
        'socialite_account',
        'avatar_url'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email',
        'created_at',
        'updated_at',
        'socialite_account',
        'email_verified_at',
        'player'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'socialite_account' => 'boolean'
        ];
    }

    public function player()
    {
        return $this->hasOne(Player::class);
    }

    public function socialite()
    {
        return $this->hasOne(Socialite::class);
    }

    public function getUsernameAttribute($username)
    {
        return $this->socialite_account ? $this->socialite->name : $username;
    }

    public function getAvatarUrlAttribute($avatar_url)
    {
        return $this->socialite_account ? $this->socialite->avatar_url : $avatar_url;
    }

    // TODO
    public function getIsAdminAttribute()
    {
        $admins = ['admin@joker.local'];

        return in_array($this->email, $admins, true);
    }
}
