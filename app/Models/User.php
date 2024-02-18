<?php

namespace App\Models;

use App\Models\Task;
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

    protected $fillable = [
        self::NAME,
        self::EMAIL,
        self::PASSWORD,
        self::EMAIL_VERIFIED_AT,
    ];

    protected $hidden = [
        self::PASSWORD,
        self::REMEMBER_TOKEN,
    ];

    protected $casts = [
        self::EMAIL_VERIFIED_AT => 'datetime',
        self::ADMIN => 'boolean',
    ];

    protected $relations = [
        'tasks'
    ];

    // Attributes getters/setters.

    public function setPasswordAttribute($password)
    {
        // Hash password before saving to database.
        $this->attributes[self::PASSWORD] = bcrypt($password);
    }

    public function getIsAdminAttribute()
    {
        return $this->attributes[self::ADMIN];
    }

    // Relations definitions.

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
