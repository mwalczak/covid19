<?php


namespace App\DTO;


class Data
{
    public string $date;
    public string $location;
    public int $cases = 0;
    public int $deaths = 0;
    public int $newCases = 0;
    public int $newDeaths = 0;
    public ?float $deathPercent = 0.0;
    public bool $isChina = false;

    public function __construct(string $date, string $location, int $newCases = 0, int $newDeaths = 0, int $cases = 0, int $deaths = 0)
    {
        $this->date = $date;
        $this->location = $location;
        $this->newCases = $newCases;
        $this->newDeaths = $newDeaths;
        $this->cases = $cases;
        $this->deaths = $deaths;

        $this->isChina = in_array($this->location, ['China']);
        $this->calcDeathsPercent();
    }


    public static function fromArray($data): Data
    {
        return new self($data[0], $data[1], (int) $data[2], (int) $data[3], (int) $data[4], (int) $data[5]);
    }

    public function calcDeathsPercent()
    {
        $this->deathPercent = $this->cases > 0 ? 100 * round($this->deaths / $this->cases, 4) : null;
    }
}