<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\MatchModel;
use App\Models\TeamModel;

class GameModel extends Model
{
    use HasFactory;
    protected $table = 'game_models';
    protected $fillable = ['match_model_id', 'game_number', 'team_a_score', 'team_b_score', 'winner_team_id'];

    public function match()
    {
        return $this->belongsTo(MatchModel::class, 'match_model_id');
    }

    public function winner()
    {
        return $this->belongsTo(TeamModel::class, 'winner_team_id');
    }
}
