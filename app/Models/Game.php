<?php

namespace App\Models;

use App\Events\CardDealEvent;
use App\Events\GameOverEvent;
use App\Events\StartGameEvent;
use App\Events\UpdateGameEvent;
use App\Jobs\PlayerBotJob;
use App\Jobs\SyncGameJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Game extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $with = ['players', 'scores'];
    protected $hidden = ['password', 'ready', 'created_at', 'updated_at', 'creator', 'call_count', 'admin_cards'];
    protected $appends = ['except', 'to_fill'];
    protected $casts = [
        'cards' => 'array',
        'kicked_users' => 'array',
        'trump' => 'array',
        'ready' => 'array',
        'turn' => 'integer',
        'quarter' => 'integer',
        'hand_count' => 'integer',
        'admin_cards' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($game) {
            $game->scores()->delete();

            $game->players->each(function ($player) {
                $player->update([
                    'game_id' => null,
                    'position' => null,
                    'card' => null,
                    'cards' => null,
                    'disconnected' => false
                ]);
            });
        });
    }

    public function players()
    {
        return $this->hasMany(Player::class)->orderBy('position');
    }

    public function scores()
    {
        return $this->hasMany(Score::class)->orderBy('position');
    }

    public function start()
    {
        $cards = $this->setPositions();

        $this->players->each(function ($player) {
            $this->scores()->create([
                'player_id' => $player->id,
                'position' => $player->position
            ]);
        });

        $this->deal();

        $this->refresh();

        broadcast(new StartGameEvent($this, $cards));
        SyncGameJob::dispatch($this->id)->delay(now()->addSeconds(30));

        if ($this->players[0]->disconnected) {
            PlayerBotJob::dispatch($this->players[0], $this)->delay(now()->addSeconds(count($cards) + 3));
        }
    }

    /**
     * tu chamosul kartebshi jokeria da chamosuli kartma mojokra
     * kartis strength gavzardot 17-ze rom cagebisas gamoitvalos
     * tu nije gaaketa strength shevamcirot 1-ze rom ar caigos
     * danarchen shemtxvevashi davamatot karti rogorc aris
     * @param $card
     */
    public function addCard($card, $player)
    {
        if ($this->jokInCards() && isset($card['action']) && $card['action'] == 'mojokra') {
            $card['strength'] = 17;
        } elseif (isset($card['action']) && $card['action'] == 'kvevidan') {
            $card['strength'] = 1;
        }

        $cards = $this->cards;
        array_push($cards, $card);
        $this->cards = $cards;
        $this->save();

        $player->update(['card' => $card]);

        $this->refresh();

        return $card;
    }

    /**
     * tu 4 ive motamashe chamovida eseigi unda gavigot vin caigo
     * shevadarot motamasheebis bolo chamosuli kartebi yvelaze magal karts
     * vinc caigebs bazashi davafiksirot da chamosvlis poziciashi davamatot
     *
     * tu yvela karti chamosuli shevamocmot bazashi motamasheebis kartebi unda iyos null
     * mashin gamovtvalot scores da shevamocmot tu morcha pulka an tamashi
     *
     * sxva shemtxvevashi ubralod chamomsvlelis pozicia ganvaaxlot
     */
    public function checkTake()
    {
        if (count($this->cards) == 4) {
            $highestCard = $this->highestCard();
            $position = -1;
            foreach ($this->players as $player) {
                if ($player->card == $highestCard) {
                    $this->scores[$player->position]->incrementTake($this->quarter);
                    $this->update(['turn' => $player->position]);
                    $position = $player->position;
                }
                $player->update(['card' => null]);
            }

            return $position;
        } else {
            $this->updateTurn();
            return false;
        }
    }

    public function checkEndOfTheHand()
    {
        if (empty($this->players()->pluck('cards')->whereNotNull()->toArray())) {
            $this->calcScoresAfterHand();

            return $this->checkEnd();
        }
    }

    /**
     * tu piveli karti jokeria anu acxada magali an caigos am shemtxvevashi
     * tu kartebshi koziria da magali koziri ar ucxadebia jokeri amovigot kartebidan radgan ver caigebs jokeri
     * shemdeg tu caigos acxada da es cveti aris kartebshi amovigot jokeri kartebidan
     *
     * tu bezia araa da koziria kartebshi mashin vfiltravt kozirze tu ara mashin pirveli kartis cvetze
     * valagebt kartebs sididis mixedvit da pirveli kartia yvelaze magali romelmac caigo
     * @return mixed
     */
    public function highestCard()
    {
        $suit = null;
        $cards = $this->cards;
        if (isset($cards[0]['action'])) {
            if ($this->trumpInCards() && $cards[0]['actionsuit'] != $this->trump['suit']) {
                array_shift($cards);
                $suit = $this->trump['suit'];
            } else if ($cards[0]['action'] == 'caigos' && $this->suitInCards($cards[0]['actionsuit'])) {
                $card = array_shift($cards);
                $suit = $card['actionsuit'];
            }
        } else {
            if ($this->trump['strength'] != 16 && $this->trumpInCards()) {
                $suit = $this->trump['suit'];
            } else {
                $suit = $cards[0]['suit'];
            }
        }

        $cards = array_filter($cards, function($card) use ($suit){
            return $card['suit'] == $suit || $card['strength'] == 16 || $card['strength'] == 17;
        });

        $cards = collect($cards)->sortByDesc('strength');
        $this->update(['cards' => []]);

        return $cards->values()->all()[0];
    }

    /**
     * tu bolo pulkaa da bolo darigeba gamovtvalot da davamtavrot tamashi
     *
     * tu 9-ianebia da bolo darigebaa an tu meore pulkaa da bolo darigebaa an tu 8 darigebaa eseigi pulkis boloa
     * gamovtvalot da gavagrdzelot tamashi
     *
     * sxva shemtxvevashi shemdegi darigeba davarigot
     * @return bool
     */
    public function checkEnd()
    {
        if ($this->quarter == 4 && $this->hand_count == 4) {
            $this->calcScoresAfterQuarter();
            return true;
        }
        // pulkebis bolo darigebebi
        if (($this->type == 9 && $this->hand_count == 4) || ($this->quarter == 2 && $this->hand_count == 4) || $this->hand_count == 8) {
            $this->calcScoresAfterQuarter();
            $this->update(['quarter' => $this->quarter + 1, 'hand_count' => 1, 'turn' => 0]);
            $this->deal();
            $this->refresh();
            broadcast(new UpdateGameEvent($this));
            return false;
        }

        $this->update([
            'hand_count' => $this->hand_count + 1,
            'turn' => $this->turnPosition(),
            'state' => 'dealing',
            'trump' => null
        ]);

        $this->deal();
        $this->refresh();
        broadcast(new UpdateGameEvent($this));
        return false;
    }

    public function finishGame()
    {
        $scores = $this->scores;

        $scores = $scores->map(function ($score) {
            return ['position' => $score->position, 'result' => $score->getData('result', 4, 5)];
        });

        $scores = $scores->sortByDesc('result')->values()->all();

        for ($i = 0; $i < 4; $i++) {
            if ($i == 0 || $i == 1) {
                $this->players[$scores[$i]['position']]->increment('games_won');
            }

            $this->players[$scores[$i]['position']]->increment('games_played');
        }

        $this->update(['state' => 'finished', 'trump' => null, 'turn' => 4]);
        $this->refresh();

        broadcast(new GameOverEvent($this, $scores));

        $this->players->each(function ($player) {
            $player->update([
                'game_id' => null,
                'position' => null,
                'card' => null,
                'cards' => null,
                'disconnected' => false
            ]);
        });
    }

    public function calcScoresAfterHand()
    {
        $all = $this->numCardsToDeal();
        $penalty = $this->penalty;

        $this->scores->each(function ($score) use ($all, $penalty) {
            $score->calcHandResult($this->quarter, $all, $penalty);
        });
    }

    public function calcScoresAfterQuarter()
    {
        $perfectNum = $this->numPerfectInScores();

        switch ($perfectNum) {
            case 1:
                foreach($this->scores as $score) {
                    $sum = $score->sumResult($this->quarter);
                    $max = $score->maxResult($this->quarter);
                    if ($score->perfectResults($this->quarter)) {
                        $result = $sum + $max;
                        $color = 'y';
                    } else {
                        $result = $sum - $max;
                        $color = 'r';
                    }

                    $score->updateColor($this->quarter, $color, $max);
                    $this->createResult($score, $result);
                }
                break;
            case 2:
                foreach($this->scores as $score) {
                    $sum = $score->sumResult($this->quarter);
                    $max = $score->maxResult($this->quarter);
                    if ($score->perfectResults($this->quarter)) {
                        $result = $sum + $max;
                        $color = 'y';
                    } else {
                        $result = $sum;
                    }

                    $score->updateColor($this->quarter, $color, $max);
                    $this->createResult($score, $result);
                }
                break;
            default:
                foreach($this->scores as $score) {
                    $result = $score->sumResult($this->quarter);

                    $this->createResult($score, $result);
                }
                break;
        }
    }

    public function deal()
    {
        $deck = new Deck;
        $num = $this->numCardsToDeal();
        $max = $num * 4;
        $cards = [[], [], [], []];

        switch ($this->turn) {
            case 0:
                $positions = [0, 1, 2, 3];
                break;
            case 1:
                $positions = [3, 0, 1, 2];
                break;
            case 2:
                $positions = [2, 3, 0, 1];
                break;
            default:
                $positions = [1, 2, 3, 0];
                break;
        }

        if ($this->admin_cards != null) {
            // just a security check 
            if (count($this->admin_cards['cards']) > $num) {
                $admin_cards = array_slice($this->admin_cards['cards'], 0, $num);
            } else {
                $admin_cards = $this->admin_cards['cards'];
            }
            
            // assign cheaters cards and account for it
            $cards[$positions[$this->admin_cards['position']]] = $admin_cards;
            $max = $max - count($admin_cards);
            
            // $i = card, $j = player
            for ($i = 0, $j = 0; $i < $max; $i++) {
                // if next card is cheaters card skip it and increment iteration
                if (in_array($deck->cards[$i], $admin_cards, true)) {
                    $max++;
                    continue;
                }
                // if player has not enough cards give one more
                if (count($cards[$j]) != $num) {
                    array_push($cards[$j], $deck->cards[$i]);
                } else {
                    // iff both above checks failed
                    // repeat the same for the next player
                    $i--;
                }
                // go to the next player
                $j = $j == 3 ? 0 : $j + 1;
            }

            $this->admin_cards = null;
        } else {
            // just deal cards normally
            for ($i = 0, $j = 0; $i < $max; $i++) {
                array_push($cards[$j], $deck->cards[$i]);
                $j = $j == 3 ? 0 : $j + 1;
            }
        }

        $this->players->each(function ($player, $pos) use ($cards, $positions) {
            $player->update(['cards' => $cards[$positions[$pos]]]);
        });

        // check cheaters cards conflict with trump card
        $trump = $deck->cards[$max] ?? null;
        while ($trump !== null && isset($admin_cards) && in_array($trump, $admin_cards, true)) {
            $max++;
            $trump = $deck->cards[$max] ?? null;
        }

        $this->setTrump($num, $trump);
    }

    private function setTrump($num, $card)
    {
        if ($num != 9) {
            $this->update(['state' => 'call', 'trump' => $card]);
            $this->players->each(function ($player) {
                broadcast(new CardDealEvent($player->user_id, $player->cards));
            });
        } else {
            $this->update(['state' => 'trump']);
            $this->players->each(function ($player) {
                if ($this->turn == $player->position) {
                    broadcast(new CardDealEvent($player->user_id, array_slice($player->cards, 0, 3), true));
                } else {
                    broadcast(new CardDealEvent($player->user_id, array_slice($player->cards, 0, 3)));
                }
            });
        }
    }

    protected function turnPosition()
    {
        return $this->hand_count >= 4 ? $this->hand_count - 4 : $this->hand_count;
    }

    public function numCardsToDeal()
    {
        if ($this->quarter % 2 == 0 || $this->type == 9) return 9;
        if ($this->quarter == 3 && $this->type == 1) return 9 - $this->hand_count;

        return $this->hand_count;
    }

    public function numPerfectInScores()
    {
        $count = 0;
        foreach($this->scores as $score)
        {
            if ($score->perfectResults($this->quarter)) $count++;
        }

        return $count;
    }

    public function jokInCards()
    {
        if (! $this->cards) return false;
        foreach ($this->cards as $card) {
            if ($card['strength'] == 16) {
                return true;
            }
        }

        return false;
    }

    public function suitInCards($suit)
    {
        foreach ($this->cards as $card) {
            if ($card['suit'] == $suit) {
                return true;
            }
        }

        return false;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function addPlayer(User $user)
    {
        $user->player->update(['game_id' => $this->id, 'position' => $this->players()->count()]);
    }

    public function path()
    {
        return '/games/' . $this->id;
    }

    //atuzva
    public function setPositions()
    {
        $deck = (new Deck)->lastPlayer();
        $lastPlayer = $deck['pos'];

        switch ($lastPlayer) {
            case 1:
                $positions = [3, 0, 1, 2];
                break;
            case 2:
                $positions = [2, 3, 0, 1];
                break;
            case 3:
                $positions = [1, 2, 3, 0];
                break;
            default:
                $positions = [0, 1, 2, 3];
                break;
        }

        for ($i = 0; $i < 4; $i++) {
            $this->players[$i]->setPosition($positions[$i]);
        }

        return $deck['cards'];
    }

    protected function trumpInCards()
    {
        if ($this->trump['strength'] == 16) return false;
        foreach ($this->cards as $card) {
            if($card['suit'] == $this->trump['suit']) return true;
        }

        return false;
    }

    public function determinePosition()
    {
        $positions = $this->players()->pluck('position')->toArray();

        for ($position = 1; $position < 4; $position++) {
            if (! in_array($position, $positions, true)) return $position;
        }
    }

    public function updateTurn()
    {
        $turn = $this->turn == 3 ? 0 : $this->turn + 1;

        $this->update(['turn' => $turn]);
    }

    public function updateCallCount()
    {
        if ($this->call_count == 3) {
            $this->update(['call_count' => 0, 'state' => 'card']);
        } else {
            $this->increment('call_count');
        }
    }

    public function exceptCall()
    {
        if ($this->call_count != 3) return -1;

        $sum = $this->scores->sum(function ($score) {
            return $score->getData('call', $this->quarter, $this->hand_count);
        });
        $max = $this->numCardsToDeal();

        return $max - $sum;
    }

    public function getToFillAttribute()
    {
        if ($this->call_count >= 3) return -1;

        $sum = $this->scores->sum(function ($score) {
            return $score->getData('call', $this->quarter, $this->hand_count);
        });
        $max = $this->numCardsToDeal();

        return $max - $sum;
    }

    public function getExceptAttribute()
    {
        return $this->exceptCall();
    }

    public function reposition()
    {
        $this->players->each(function ($player, $key) {
            $player->setPosition($key);
        });
    }

    public function addReadyPlayer($id, $ready)
    {
        $gameready = $this->ready;

        if ($ready == 1) {
            $gameready['count']++;
        }

        array_push($gameready['players'], $id);

        $this->ready = $gameready;
        $this->save();
    }

    public function kick($pos) {
        $player = $this->players[$pos];
        $player->update(['game_id' => null, 'position' => null]);
        $kicked = $this->kicked_users;
        array_push($kicked, $player->user_id);
        $this->update(['kicked_users' => $kicked]);
        $this->refresh();
        $this->reposition();
    }

    public function createResult($score, $result)
    {
        if ($this->quarter > 1) {
            $prevResult = $score->maxResult($this->quarter - 1);
            $result += $prevResult;
        }

        $score->createResult($this->quarter, $result);
    }
}
