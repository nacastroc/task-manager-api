<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const DUE_DATE = 'due_date';

    protected $fillable = [
        self::TITLE,
        self::DESCRIPTION,
        self::DUE_DATE,
    ];

    protected $casts = [
        self::DUE_DATE => 'datetime'
    ];

    protected $relations = [
        'user'
    ];

    // Relations definitions.

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
