<?php

namespace App\Events;

use App\Models\MatchModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public MatchModel $match;

    public function __construct(MatchModel $match)
    {
        $this->match = $match;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('match.' . $this->match->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'score.updated';
    }
}