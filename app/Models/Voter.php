<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Voter extends Authenticatable
{
    use HasApiTokens, HasFactory;

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
    ];


    protected $casts = [
        'name' => 'encrypted',
        'phone' => 'encrypted',
        'pfNumber' => 'encrypted',
        'email' => 'encrypted',
        'secret' => 'encrypted',
        'inline_url' => 'encrypted',
        'google_id' => 'encrypted',

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
