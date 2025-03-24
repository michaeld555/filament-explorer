<?php

namespace Michaeld555\FilamentExplorer\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExplorerFileSaved
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public $filename
    ) {}

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
    
}
