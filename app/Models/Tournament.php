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

class Tournament extends Model
{
    use HasFactory;
    protected $table = 'tournaments';
    protected $fillable = [
        'venue_id',
        'name',
        'format',
        'start_date',
        'end_date',
        'status',
    ];

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
