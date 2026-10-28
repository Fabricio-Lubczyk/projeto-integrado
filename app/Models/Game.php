<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'developer', 'released_on', 'description'];

    protected function casts(): array
    {
        return ['released_on' => 'date'];
    }
}
