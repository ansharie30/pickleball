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
use App\Models\Court;
use App\Models\GameModel;

class MatchModel extends Model
{
    use HasFactory;
    protected $table = 'match_models';
    protected $fillable = ['tournament_id', 'division_id', 'court_id', 'team_a_id', 'team_b_id', 'round', 'status', 'winner_team_id', 'scheduled_at'];
    
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function teamA()
    {
        return $this->belongsTo(TeamModel::class, 'team_a_id');
    }

    public function teamB()
    {
        return $this->belongsTo(TeamModel::class, 'team_b_id');
    }

    public function winner()
    {
        return $this->belongsTo(TeamModel::class, 'winner_team_id');
    }

    public function games()
    {
        return $this->hasMany(GameModel::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
