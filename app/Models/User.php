<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    const NAME = 'name';
    const EMAIL = 'email';
    const PASSWORD = 'password';
    const EMAIL_VERIFIED_AT = 'email_verified_at';
    const REMEMBER_TOKEN = 'remember_token';
    const ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        self::NAME,
        self::EMAIL,
        self::PASSWORD,
        self::EMAIL_VERIFIED_AT,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        self::PASSWORD,
        self::REMEMBER_TOKEN,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        self::EMAIL_VERIFIED_AT => 'datetime',
        self::ADMIN => 'boolean',
    ];

    public function setPasswordAttribute($password)
    {
        // Hash password before saving to database.
        $this->attributes[self::PASSWORD] = bcrypt($password);
    }

    public function getIsAdminAttribute()
    {
        return $this->attributes[self::ADMIN];
    }
}
