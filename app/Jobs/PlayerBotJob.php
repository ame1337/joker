<?php

namespace App\Jobs;

use App\Models\PlayerBot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PlayerBotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $player, $game;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($player, $game)
    {
        $this->player = $player;
        $this->game = $game;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->player->refresh();
        if ($this->player->has_bot_kicked || !$this->player->disconnected) return;
        $this->player->update(['has_bot_kicked' => true]);
        $bot = new PlayerBot($this->player, $this->game);
        $method = $this->game->state;
        if (method_exists($bot, $method)) {
            $bot->$method();
        }
        $this->player->update(['has_bot_kicked' => false]);
    }
}
