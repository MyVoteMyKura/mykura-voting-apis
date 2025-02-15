<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Voter extends Authenticatable
{
    use HasApiTokens;

    protected $fillable =[
        'name',
        'phone',
        'pfNumber',
        'email',
        'email_verified',
        'google_id',
        'picture_url',
        'ip_address',
        'inline_url',
        'secret',
        'balance'
    ];


    protected $casts = [
        'name' => 'encrypted',
        'phone' => 'encrypted',
        'pfNumber' => 'encrypted',
        'email' => 'encrypted',
        'secret' => 'encrypted',
        'inline_url' => 'encrypted',
        'google_id' => 'encrypted',
        'balance' => 'encrypted'

    ];

   function position()
   {
       return $this->belongsTo(Position::class);
   }

   function votes()
   {
       return $this->hasMany(Vote::class, 'user_id');
   }



}
