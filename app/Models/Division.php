<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Tournament;
use App\Models\TeamModel;

class Division extends Model
{
    use HasFactory;
    protected $table = 'divisions';
    protected $fillable = ['tournament_id', 'name', 'skill_level'];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function teams()
    {
        return $this->hasMany(TeamModel::class);
    }
}
