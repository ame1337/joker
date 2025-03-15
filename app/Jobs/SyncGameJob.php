<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\SyncGameStateEvent;
use App\Models\Game;

class SyncGameJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $game_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($game_id)
    {
        $this->game_id = $game_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $game = Game::Find($this->game_id);
        if ($game !== null) {
            $states = ['trump', 'call', 'card'];
            if (in_array($game->state, $states, true)) {
                broadcast(new SyncGameStateEvent($game));
            }

            $this->dispatch($game->id)->delay(now()->addSeconds(30));
        }
    }
}
