<?php


namespace App\Service\Data;


use App\DTO\Data;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class DataProvider
{
    private string $dataDirectory;
    private LoggerInterface $logger;
    private bool $includeChina = false;
    private bool $includeWorld = false;
    private int $casesThreshold = 0;
    private int $deathsThreshold = 0;
    private array $filterLocations = [];
    private bool $groupByDateAndLocation = false;
    private array $uniqueLocations = [];

    public function __construct(string $dataDirectory, LoggerInterface $logger)
    {
        $this->dataDirectory = $dataDirectory;
        $this->logger = $logger;
    }

    public function filterLocations(array $locations): DataProvider
    {
        $this->filterLocations = $locations;

        return $this;
    }

    public function setChina(bool $include): DataProvider
    {
        $this->includeChina = $include;

        return $this;
    }

    public function setWorld(bool $include): DataProvider
    {
        $this->includeWorld = $include;

        return $this;
    }

    public function setCasesThreshold(int $casesThreshold = 100): DataProvider
    {
        $this->casesThreshold = $casesThreshold;

        return $this;
    }

    public function setDeathsThreshold(int $deathsThreshold = 100): DataProvider
    {
        $this->deathsThreshold = $deathsThreshold;

        return $this;
    }

    public function groupByNameAndLocation(): DataProvider
    {
        $this->groupByDateAndLocation = true;

        return $this;
    }

    public function getRecentData()
    {
        $data = [];
        $filename = $this->generateFilename();
        if (!is_file($filename)) {
            $this->downloadData();
        }
        if (($h = fopen($filename, "r")) !== FALSE) {
            $header = fgetcsv($h, 1000, ",");
            while (($line = fgetcsv($h, 1000, ",")) !== FALSE) {
                $record = Data::fromArray($line);
                if ($record->isChina && !$this->includeChina || $record->isWorld && !$this->includeWorld ||
                    $this->casesThreshold && $record->cases < $this->casesThreshold || $this->deathsThreshold && $record->deaths < $this->deathsThreshold ||
                    !empty($this->filterLocations) && !in_array($record->location, $this->filterLocations)) {
                    continue;
                }
                if($this->groupByDateAndLocation){
                    $data[$record->date][$record->location] = $record;
                } else {
                    $data[] = $record;
                }
                if(!in_array($record->location, $this->uniqueLocations)){
                    $this->uniqueLocations[] = $record->location;
                }
            }
            fclose($h);
        }
        if($this->groupByDateAndLocation){
            $this->normalizeData($data);
        }

        return $data;
    }

    private function normalizeData(array &$data)
    {
        foreach($data as $date => $locationsData){
            foreach($this->filterLocations as $location){
                if(empty($locationsData[$location])){
                    $locationsData[$location] = new Data($date, $location);
                }
            }
            ksort($locationsData);
            $data[$date] = $locationsData;
        }
    }

    public function getLocationsCount(): int
    {
        return count($this->uniqueLocations);
    }

    public function getDataTime(): ?\DateTime
    {
        $filename = $this->generateFilename();
        if ($time = filectime($filename)) {
            return new \DateTime('@' . $time);
        }

        return null;
    }

    private function generateFilename()
    {
        return $this->dataDirectory . '/' . (new \DateTime())->format('Y-m-d');
    }

    private function downloadData()
    {
        try {
            $client = HttpClient::create();
            $response = $client->request('GET', 'https://covid.ourworldindata.org/data/full_data.csv');

            file_put_contents($this->generateFilename(), $response->getContent());
        } catch (ExceptionInterface $e) {
            $this->logger->warning('download exception: ' . $e->getMessage());
        }
    }
}