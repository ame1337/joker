<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $guarded = [];
    protected $with = ['user:id,name', 'scores'];
    protected $hidden = ['cards'];
    protected $casts = [
        'cards' => 'array',
        'card' => 'array'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    /**
     * Updates players position
     *
     * @param $position
     */
    public function setPosition($position)
    {
        $this->update(['position' => $position]);
    }

    /**
     * Checks if a player can play submitted card
     *
     * jer vigebt tu pirveli chamosvlaa da an magali acxada an caigos true
     * shemdeg vigebt tu magalia nacxadebi da magali karti chamovida true
     * tu araa magali nacxadebi mashin cveti tu emtxveva true;
     * tu mojokra an nije true
     * tu cveti ar yavs da koziria true
     * tu arc cveti yavs da arc koziri true
     *
     * @param $card
     * @param $gameCards
     * @param $trump
     * @return bool
     */
    public function canPlay($card, $gameCards, $trump)
    {
        if ((empty($gameCards) && $card['strength'] !== 16) || (empty($gameCards) &&
                isset($card['action']) && in_array($card['action'], ['magali', 'caigos']))) {
            return true;
        } else {
            $suit = isset($gameCards[0]['actionsuit']) ? $gameCards[0]['actionsuit'] : $gameCards[0]['suit'];

            if (isset($gameCards[0]['action']) && ($gameCards[0]['action'] === 'magali')) {
                if ($this->isHighestSuitInCards($card, $suit)) return true;
            } else if ($card['suit'] === $suit) return true;

            if (isset($card['action']) && in_array($card['action'], ['mojokra', 'nije'])) return true;

            if ($this->suitNotInCards($suit) && ($card['suit'] === $trump)) return true;

            if ($this->suitNotInCards($suit) && $this->suitNotInCards($trump)) return true;
        }

        return false;
    }

    /**
     * Checks if a card is highest of its suit in players cards
     *
     * @param $card
     * @param $suit
     * @return bool
     */
    public function isHighestSuitInCards($card, $suit)
    {
        $cards = array_filter($this->cards, function($card) use ($suit) {
            return $card['suit'] === $suit;
        });

        if (empty($cards)) return false;

        $cards = collect($cards)->sortByDesc('strength');

        $highestCard = $cards->values()->all()[0];
        if (($highestCard['strength'] === $card['strength']) && ($highestCard['suit'] === $card['suit'])) return true;

        return false;
    }

    /**
     * Checks if suit is in players cards
     *
     * @param $suit
     * @return bool
     */
    public function suitNotInCards($suit)
    {
        foreach($this->cards as $card) {
            if ($card['suit'] === $suit) return false;
        }

        return true;
    }

    /**
     * @param $card
     */
    public function removeCard($card)
    {
        $cards = $this->cards;
        $cardIndex = array_search($card, $cards);
        array_splice($cards, $cardIndex, 1);

        $this->cards = empty($cards) ? null : $cards;
        $this->save();
    }
}
