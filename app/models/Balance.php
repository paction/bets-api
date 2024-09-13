<?php
namespace App\Models;

class Balance extends Model
{
    protected $table = 'balances';

    /**
     * Loads all user's balances in all currencies and returns as an array
     */
    public function load($userId): array
    {
        $stmt = self::$pdo->prepare("SELECT balance, currency FROM $this->table WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Transform the result - 'currency' becomes the key
        $balances = [];
        foreach ($rows as $row) {
            $balances[$row['currency']] = $row['balance'];
        }

        $this->attributes['balances'] = $balances;

        return $balances;
    }

    public function findByUserIdAndCurrency($userId, $currency): array
    {
        $stmt = self::$pdo->prepare("SELECT * FROM $this->table WHERE user_id = :user_id AND currency = :currency");
        $stmt->execute(['user_id' => $userId, 'currency' => $currency]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            $this->attributes = $result;
            $this->isNew = false;
        }

        return $result;
    }
}