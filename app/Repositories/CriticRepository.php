<?php
namespace App\Repositories;

use App\Models\Critic;

class CriticRepository
{
    public function create(array $data): Critic
    {
        return Critic::create($data);
    }

    public function hasAlreadyCriticized(int $userId, int $filmId): bool
    {
        return Critic::where('user_id', $userId)->where('film_id', $filmId)->exists();
    }
}
