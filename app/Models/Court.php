<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Venue;
use App\Models\MatchModel;

class Court extends Model
{
    use HasFactory;
    protected $table = 'courts';
    protected $fillable = ['venue_id', 'name', 'qr_code_token', 'status'];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function matches()
    {
        return $this->hasMany(MatchModel::class, 'court_id');
    }
}
