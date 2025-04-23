<?php 
namespace App\Repositories;

use App\Models\Film;

class FilmRepository
{
    public function create(array $data): Film
    {
        return Film::create($data);
    }

    public function update(Film $film, array $data): Film
    {
        $film->update($data);
        return $film;
    }

    public function delete(Film $film): void
    {
        $film->critics()->delete();

        $film->delete();
    }

 
}
