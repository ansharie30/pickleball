<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Court;
use App\Models\Tournament;

class Venue extends Model
{
    use HasFactory;
    protected $table = 'venues';
    protected $fillable = ['owner_id', 'name', 'address'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function courts()
    {
        return $this->hasMany(Court::class);
    }

    public function tournaments()
    {
        return $this->hasMany(Tournament::class);
    }
}
