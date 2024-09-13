<?php
namespace App\Models;

use App\Models\Balance;

class User extends Model
{
    protected $table = 'users';

    public function authenticate($username, $password)
    {
        $stmt = self::$pdo->prepare("SELECT * FROM $this->table WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $userObject = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($userObject) {
            $this->attributes = $userObject;
            $this->isNew = false;
            if (password_verify($password, $this->password)) return true;
        }

        return false;
    }

    public function authenticateByToken($token)
    {
        $stmt = self::$pdo->prepare("SELECT * FROM $this->table WHERE token = :token");
        $stmt->execute(['token' => $token]);
        $userObject = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($userObject) {
            $this->attributes = $userObject;
            $this->attributes['balances'] = $this->getAllBalances();
            $this->isNew = false;
            return true;
        }

        return false;
    }

    /**
     * Generate a secure random auth token and save the user record
     */
    public function generateToken()
    {
        $this->token = bin2hex(random_bytes(16));

        // Set expiration time to 1 hour from now
        $expiresAt = new \DateTime();
        $expiresAt->add(new \DateInterval('PT1H')); 
        $this->token_expires_at = $expiresAt->format('Y-m-d H:i:s');

        $this->save();
    }

    public function findByUsername($username)
    {
        try {
            $stmt = self::$pdo->prepare("SELECT * FROM $this->table WHERE username = :username");
            $stmt->execute(['username' => $username]);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($result) {
                $this->attributes = $result;
                $this->isNew = false;
                return $this;
            }
        } catch (\Exception $e) {
            die("User not found");
        }
    }

    /**
     * Returns user's balances in all currencies in cents
     */
    public function getAllBalances(): array
    {
        $balanceModel = new Balance();
        $this->balances = $balanceModel->load($this->id);
        return $this->balances;
    }

    /**
     * Returns user's balances in a specific currency in cents
     */
    public function getBalance($currency): int
    {
        if(!isset($this->balances)) $this->getAllBalances();

        return isset($this->balances[$currency]) ? $this->balances[$currency] : 0;
    }
}