<?php

namespace App\Helpers;

class UsersData
{
    const USERS = [
        [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'created_at' => '2025-08-16 12:00:00',
            'updated_at' => '2025-08-16 12:00:00',
        ],
        [
            'id' => 2,
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'created_at' => '2025-08-16 12:05:00',
            'updated_at' => '2025-08-16 12:05:00',
        ],
        [
            'id' => 3,
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'created_at' => '2025-08-16 12:10:00',
            'updated_at' => '2025-08-16 12:10:00',
        ],
        [
            'id' => 4,
            'name' => 'Bob Williams',
            'email' => 'bob@example.com',
            'created_at' => '2025-08-16 12:15:00',
            'updated_at' => '2025-08-16 12:15:00',
        ],
        [
            'id' => 5,
            'name' => 'Charlie Brown',
            'email' => 'charlie@example.com',
            'created_at' => '2025-08-16 12:20:00',
            'updated_at' => '2025-08-16 12:20:00',
        ],
        [
            'id' => 6,
            'name' => 'Diana Prince',
            'email' => 'diana@example.com',
            'created_at' => '2025-08-16 12:25:00',
            'updated_at' => '2025-08-16 12:25:00',
        ],
        [
            'id' => 7,
            'name' => 'Ethan Hunt',
            'email' => 'ethan@example.com',
            'created_at' => '2025-08-16 12:30:00',
            'updated_at' => '2025-08-16 12:30:00',
        ],
        [
            'id' => 8,
            'name' => 'Fiona Gallagher',
            'email' => 'fiona@example.com',
            'created_at' => '2025-08-16 12:35:00',
            'updated_at' => '2025-08-16 12:35:00',
        ],
        [
            'id' => 9,
            'name' => 'George Martin',
            'email' => 'george@example.com',
            'created_at' => '2025-08-16 12:40:00',
            'updated_at' => '2025-08-16 12:40:00',
        ],
        [
            'id' => 10,
            'name' => 'Hannah Baker',
            'email' => 'hannah@example.com',
            'created_at' => '2025-08-16 12:45:00',
            'updated_at' => '2025-08-16 12:45:00',
        ],
    ];

    public static function all(): array
    {
        return self::USERS;
    }

    public static function findById(int $id): ?array
    {
        foreach (self::USERS as $user) {
            if ($user['id'] === $id) return $user;
        }
        return null;
    }
}


class UserTransactions
{
    const TRANSACTIONS = [
        ['transaction_id' => 'T-01', 'user_id' => 1, 'amount' => 500, 'type' => 'credit', 'created_at' => '2025-08-16 13:00:00'],
        ['transaction_id' => 'T-02', 'user_id' => 1, 'amount' => 200, 'type' => 'debit', 'created_at' => '2025-08-16 14:00:00'],
        ['transaction_id' => 'T-03', 'user_id' => 2, 'amount' => 1000, 'type' => 'credit', 'created_at' => '2025-08-16 13:30:00'],
        ['transaction_id' => 'T-04', 'user_id' => 3, 'amount' => 750, 'type' => 'credit', 'created_at' => '2025-08-16 14:15:00'],
        ['transaction_id' => 'T-05', 'user_id' => 4, 'amount' => 300, 'type' => 'debit', 'created_at' => '2025-08-16 15:00:00'],
        ['transaction_id' => 'T-06', 'user_id' => 5, 'amount' => 450, 'type' => 'credit', 'created_at' => '2025-08-16 15:30:00'],
        ['transaction_id' => 'T-07', 'user_id' => 6, 'amount' => 600, 'type' => 'debit', 'created_at' => '2025-08-16 16:00:00'],
        ['transaction_id' => 'T-08', 'user_id' => 7, 'amount' => 250, 'type' => 'credit', 'created_at' => '2025-08-16 16:30:00'],
        ['transaction_id' => 'T-09', 'user_id' => 8, 'amount' => 800, 'type' => 'debit', 'created_at' => '2025-08-16 17:00:00'],
        ['transaction_id' => 'T-10', 'user_id' => 9, 'amount' => 1200, 'type' => 'credit', 'created_at' => '2025-08-16 17:30:00'],
        ['transaction_id' => 'T-11', 'user_id' => 10, 'amount' => 400, 'type' => 'debit', 'created_at' => '2025-08-16 18:00:00'],
    ];

    // Get all transactions
    public static function all(): array
    {
        return self::TRANSACTIONS;
    }

    // Get transactions for a specific user
    public static function getByUserId(int $userId): array
    {
        return array_filter(self::TRANSACTIONS, fn($txn) => $txn['user_id'] === $userId);
    }

    // Get transaction by transaction ID
    public static function getByTransactionId(string $transactionId): ?array
    {
        foreach (self::TRANSACTIONS as $txn) {
            if ($txn['transaction_id'] === $transactionId) return $txn;
        }
        return null;
    }

    // Calculate total balance for a user
    public static function getBalanceByUserId(int $userId): int
    {
        $transactions = self::getByUserId($userId);
        $balance = 0;
        foreach ($transactions as $txn) {
            $balance += $txn['type'] === 'credit' ? $txn['amount'] : -$txn['amount'];
        }
        return $balance;
    }
}

