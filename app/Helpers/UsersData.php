<?php

namespace App\Helpers;

class UsersData
{
    const USERS = [
        ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'created_at' => '2025-08-16 12:00:00', 'updated_at' => '2025-08-16 12:00:00'],
        ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'created_at' => '2025-08-16 12:05:00', 'updated_at' => '2025-08-16 12:05:00'],
        ['id' => 3, 'name' => 'Alice Johnson', 'email' => 'alice@example.com', 'created_at' => '2025-08-16 12:10:00', 'updated_at' => '2025-08-16 12:10:00'],
        ['id' => 4, 'name' => 'Bob Williams', 'email' => 'bob@example.com', 'created_at' => '2025-08-16 12:15:00', 'updated_at' => '2025-08-16 12:15:00'],
        ['id' => 5, 'name' => 'Charlie Brown', 'email' => 'charlie@example.com', 'created_at' => '2025-08-16 12:20:00', 'updated_at' => '2025-08-16 12:20:00'],
        ['id' => 6, 'name' => 'Diana Prince', 'email' => 'diana@example.com', 'created_at' => '2025-08-16 12:25:00', 'updated_at' => '2025-08-16 12:25:00'],
        ['id' => 7, 'name' => 'Ethan Hunt', 'email' => 'ethan@example.com', 'created_at' => '2025-08-16 12:30:00', 'updated_at' => '2025-08-16 12:30:00'],
        ['id' => 8, 'name' => 'Fiona Gallagher', 'email' => 'fiona@example.com', 'created_at' => '2025-08-16 12:35:00', 'updated_at' => '2025-08-16 12:35:00'],
        ['id' => 9, 'name' => 'George Martin', 'email' => 'george@example.com', 'created_at' => '2025-08-16 12:40:00', 'updated_at' => '2025-08-16 12:40:00'],
        ['id' => 10, 'name' => 'Hannah Baker', 'email' => 'hannah@example.com', 'created_at' => '2025-08-16 12:45:00', 'updated_at' => '2025-08-16 12:45:00'],
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
