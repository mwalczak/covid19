<?php


namespace App\DTO;


class Data
{
    public string $date;
    public string $location;
    public ?int $cases;
    public ?int $deaths;
    public ?int $newCases;
    public ?int $newDeaths;
    public ?float $deathPercent;
    public bool $isChina = false;
    public bool $isWorld = false;

    public function __construct(string $date, string $location, int $newCases = null, int $newDeaths = null, int $cases = null, int $deaths = null)
    {
        $this->date = $date;
        $this->location = $location;
        $this->newCases = $newCases;
        $this->newDeaths = $newDeaths;
        $this->cases = $cases;
        $this->deaths = $deaths;

        $this->isWorld = in_array($this->location, ['World', 'International']);
        $this->isChina = in_array($this->location, ['China']);
        $this->deathPercent = $this->cases > 0 ? 100 * round($this->deaths / $this->cases, 4) : null;
    }


    public static function fromArray($data): Data
    {
        return new self($data[0], $data[1], (int) $data[2], (int) $data[3], (int) $data[4], (int) $data[5]);
    }
}