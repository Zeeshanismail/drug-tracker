<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
    use HasFactory;
    protected $fillable = ['rxcui', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
