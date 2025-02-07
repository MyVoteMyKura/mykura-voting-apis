<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'email', 'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'email' => 'encrypted'
    ];

    protected  $role = 'admin';

}
