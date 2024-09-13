<?php
namespace App\Controllers;

use App\Models\Bet;
use App\Models\User;

class BetController extends BaseController 
{

    public function __construct()
    {
        $this->rules = [
            'token' => ['required' => true, 'min-length' => '32', 'max-length' => '32'],
            'amount' => ['required' => true, 'max-length' => '8', 'min-length' => '2', 'min-value' => '1', 'type' => 'numeric'],
            'currency' => ['required' => true, 'max-length' => '3', 'min-length' => '3'],
        ];
        
        parent::__construct();
    }

    /**
     * Empty action, does nothing and chills out.
     */
    public function index()
    {
        $this->jsonResponse([]);
    }

    /**
     * Action for a bet request.
     * 
     * Input parameters: 
     *  token - string, required, authentication token (32 characters long)
     *  amount - integer, required, bet amount in cents
     *  currency - string, required, a 3-letters currency code
     * 
     * Example of usage:
     * curl -X POST http://localhost:8090/bet -d "token=328d9005190a199fe5901f1a94e2aac0" -d "amount=578" -d "currency=USD"
     */
    public function bet()
    {   
        $this->executeValidation();

        $user = new User();
        if(!$user->authenticateByToken($this->requestData['token'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Authentication failed (token)']);
        }

        // Making sure we have a number
        $betAmount = (int)$this->requestData['amount'];

        $betCurrency = $this->requestData['currency'];

        // Checking balance
        if($user->getBalance($this->requestData['currency']) < $betAmount) {
            $this->jsonResponse(['success' => false, 'message' => 'Insufficient balance'], 400);
        }

        $desiredRTP = 0.95; // Return to Player (RTP) is 95%
        $totalOutcomes = 14; // Based on the 'secure random number' from 0 to 13
        $generatedNumber = rand(0, 13); // Random outcome

        $payout = $this->calculatePayout($generatedNumber, $desiredRTP, $totalOutcomes, $betAmount);

        $bet = new Bet();
        $bet = $bet->createAndReduceBalance([
            'user_id' => $user->id, 
            'bet_amount' => $betAmount, 
            'bet_currency' => $betCurrency,
            'generated_number' => $generatedNumber,
            'payout' => $payout,
        ]);

        // Refresh user balances
        $user->balances = $user->getAllBalances();

        $this->jsonResponse([
            'success' => true,
            'bet' => [
                'bet_id' => $bet->id, 
                'bet_amount' => $betAmount, 
                'bet_currency' => $betCurrency, 
                'generated_number' => $generatedNumber, 
                'payout' => $payout
            ], 
            'balances' => $user->balances
        ]);
    }

    public function calculatePayout($outcome, $desiredRTP, $totalOutcomes, $betAmount) {
        $baseMultiplier = $totalOutcomes / $desiredRTP - 1;

        $outcomeMultiplier = $baseMultiplier * ($outcome + 1) / $totalOutcomes;

        // Rounding to the lower integer
        $payout = floor($betAmount * $outcomeMultiplier);

        // Ensure payouts are not negative and meet minimum payout requirements (1 cent)
        if ($payout < 0) $payout = 0;
        if ($payout < 1) $payout = 1;

        return $payout;
    }
}


