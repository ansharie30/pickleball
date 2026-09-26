<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{
    protected $fillable = ['name', 'scoring_type'];

    public function tournaments()
    {
        return $this->hasMany(Tournament::class);
    }
}