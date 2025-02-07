<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'name',
        ];

    protected $casts = [
        'name' => 'encrypted'
    ];

    function voters()
    {
        return $this->hasMany(Voter::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
