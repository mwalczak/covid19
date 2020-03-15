<?php


namespace App\DTO;


class Data
{
    public string $date;
    public string $location;
    public int $cases;
    public int $deaths;
    public int $newCases;
    public int $newDeaths;
    public ?float $deathPercent;
    public bool $isChina = false;
    public bool $isWorld = false;

    public static function fromArray($data): Data
    {
        $dataDTO = new self();
        $dataDTO->date = $data[0];
        $dataDTO->location = $data[1];
        $dataDTO->newCases = (int) $data[2];
        $dataDTO->newDeaths = (int) $data[3];
        $dataDTO->cases = (int) $data[4];
        $dataDTO->deaths = (int) $data[5];
        $dataDTO->isWorld = in_array($dataDTO->location, ['World', 'International']);
        $dataDTO->isChina = in_array($dataDTO->location, ['China']);
        $dataDTO->deathPercent = $dataDTO->cases > 0 ? 100 * round($dataDTO->deaths / $dataDTO->cases, 4) : null;

        return $dataDTO;
    }
}