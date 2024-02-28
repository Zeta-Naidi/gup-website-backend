<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $payload;
    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($payload, $user)
    {
        $this->payload = $payload;
        $this->user = $user;
    }


    public function broadcastAs()
    {
        return 'prova';
    }


    public function broadcastWith()
    {
        return [
            'ciao' => 'come va'
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $idChannel = $this->user['id'];
        return new PrivateChannel('newEvents.' . $idChannel); //Look that you don't have your channel as private (this isn't bad, is just in case that you want your channel as priv)
    }
}
