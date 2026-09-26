<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Venue;
use App\Models\Division;
use App\Models\TeamModel;
use App\Models\MatchModel;
use App\Models\Sport;

class Tournament extends Model
{
    use HasFactory;
    protected $table = 'tournaments';
    protected $fillable = [
        'sport_id', 'venue_id', 'name', 'format', 'start_date', 'end_date', 'status',
        'target_score', 'win_by_margin', 'best_of', 'team_size',
        'periods', 'period_minutes',
    ];

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function divisions()
    {
        return $this->hasMany(Division::class);
    }

    public function teams()
    {
        return $this->hasMany(TeamModel::class);
    }

    public function matches()
    {
        return $this->hasMany(MatchModel::class);
    }
}
