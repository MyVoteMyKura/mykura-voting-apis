<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Vote extends Model
{
    
    use HasUuids;

    protected $fillable = [
        'vvpat',
        'count',
        'voter_id',
        'user_id'
    ];

    protected $casts = [
//        'vvpat' => 'encrypted',
//        'count' => 'encrypted',
//        'voter_id' => 'encrypted'
    ];

    public function newUniqueId(): string
    {
        return (string) Uuid::uuid4();
    }

    public function uniqueIds(): array
    {
        return ['vvpat'];
    }

    public function voter()
    {
        return $this->belongsTo(Voter::class,'user_id','id');
    }


    public function position()
        {
        return $this->belongsTo(Position::class);
    }

}
