<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\TeamModel;

class PlayerProfile extends Model
{  
    use HasFactory;
    protected $table = 'player_profiles';
    protected $fillable = ['user_id', 'name', 'rating', 'wins', 'losses'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teams()
    {
        return $this->belongsToMany(TeamModel::class, 'team_player', 'player_profile_id', 'team_id');
    }
}
