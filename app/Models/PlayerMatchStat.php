<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerMatchStat extends Model
{
    protected $fillable = ['match_model_id', 'player_profile_id', 'team_id', 'points', 'assists', 'rebounds'];

    public function player()
    {
        return $this->belongsTo(PlayerProfile::class, 'player_profile_id');
    }

    public function team()
    {
        return $this->belongsTo(TeamModel::class, 'team_id');
    }
}