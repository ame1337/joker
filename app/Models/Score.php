<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Score extends Model
{
    use HasFactory;
    
    private $emptyResult = ['call' => null, 'take' => 0, 'result' => null, 'c' => 'w'];
    protected $guarded = [];
    protected $casts = ['data' => 'array'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $attributes = ['data' => '{"q_1": {}, "q_2": {}, "q_3": {}, "q_4": {}}'];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * update call.
     *
     * @param $quarter
     * @param $call
     * @return array
     */
    public function createCall($quarter, $call)
    {
        $data = $this->data;
        $row = $this->emptyResult;
        $row['call'] = $call;
        array_push($data["q_$quarter"], $row);

        $this->update(['data' => $data]);

        return $row;
    }

    public function createResult($quarter, $result)
    {
        $data = $this->data;
        $row = $this->emptyResult;
        $row['result'] = $result;
        array_push($data["q_$quarter"], $row);

        $this->update(['data' => $data]);
    }

    public function getData($key, $quarter, $hand_count)
    {
        return $this->data["q_$quarter"][$hand_count - 1][$key] ?? null;
    }

    public function incrementTake($quarter)
    {
        $data = $this->data;
        $key = array_key_last($data["q_$quarter"]);
        $data["q_$quarter"][$key]['take']++;

        $this->update(['data' => $data]);
    }

    public function calcHandResult($quarter, $all, $penalty)
    {
        $data = $this->data;
        $key = array_key_last($data["q_$quarter"]);
        $score = $data["q_$quarter"][$key];

        if ($score['call'] == $score['take'] && $score['call'] == $all) {
            $score['result'] = $score['call'] * 100;
        } elseif ($score['call'] == $score['take']) {
            $score['result'] = $score['call'] * 50 + 50;
        } elseif ($score['take'] == 0) {
            $score['result'] = $penalty;
        } else {
            $score['result'] = $score['take'] * 10;
        }

        $data["q_$quarter"][$key] = $score;

        $this->update(['data' => $data]);
    }

    public function perfectResults($quarter)
    {
        foreach($this->data["q_$quarter"] as $score) {
            if ($score['call'] != $score['take']) return false;
        }

        return true;
    }

    public function sumResult($quarter)
    {
        return collect($this->data["q_$quarter"])->sum('result');
    }

    public function maxResult($quarter)
    {
        return collect($this->data["q_$quarter"])->max('result');
    }

    public function updateColor($quarter, $color, $max)
    {
        $data = $this->data;
        foreach($data["q_$quarter"] as $key => $score) {
            if ($score['result'] == $max) {
                $score['c'] = $color;
                $data["q_$quarter"][$key] = $score;
                $this->update(['data' => $data]);
                break;
            }
        }
    }

}
