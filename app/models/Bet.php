<?php
namespace App\Models;

use App\Models\User;
use App\Models\Balance;

class Bet extends Model 
{
    protected $table = 'bets';

    public function createAndReduceBalance($data)
    {
        $bet = $this->create($data);

        $balanceModel = new Balance();
        $balanceModel->findByUserIdAndCurrency($data['user_id'], $data['bet_currency']);

        $balanceModel->balance = $balanceModel->balance - $data['bet_amount'];

        $balanceModel->save();

        return $bet;
    }
}