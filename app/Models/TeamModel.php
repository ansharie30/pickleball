<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Tournament;
use App\Models\Division;
use App\Models\PlayerProfile;
use App\Models\MatchModel;

class TeamModel extends Model
{
    use HasFactory;
    protected $table = 'teams';
    protected $fillable = ['tournament_id', 'division_id', 'name'];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function players()
    {
        return $this->belongsToMany(PlayerProfile::class, 'team_player', 'team_id', 'player_profile_id');
    }

    public function matchesAsTeamA()
    {
        return $this->hasMany(MatchModel::class, 'team_a_id');
    }

    public function matchesAsTeamB()
    {
        return $this->hasMany(MatchModel::class, 'team_b_id');
    }
}
