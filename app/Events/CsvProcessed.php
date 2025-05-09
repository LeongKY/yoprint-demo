<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CsvProcessed implements ShouldBroadcast
{
    public $connection = 'sync';

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $guestId;
    public string $filePath;

    /**
     * Create a new event instance.
     */
    public function __construct(string $guestId, string $filePath)
    {
        $this->guestId = $guestId;
        $this->filePath = $filePath;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('csv-status.' . $this->guestId);
    }

    public function broadcastAs(): string
    {
        return 'CsvProcessed';
    }

    public function broadcastWith(): array
    {
        return [
            'filePath' => $this->filePath,
            'message' => 'CSV processing',
        ];
    }
}
