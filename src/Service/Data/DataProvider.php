<?php


namespace App\Service\Data;


use App\DTO\Data;
use Psr\Log\LoggerInterface;
use \DateTime;

class DataProvider
{
    private string $dataDirectory;
    private LoggerInterface $logger;
    private bool $includeChina = false;
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
        /** @var Data[] $data */
        $data = [];

        foreach($this->downloadData() as $record){
            /** @var Data $record */
            if ($record->isChina && !$this->includeChina ||
                $this->casesThreshold && $record->cases < $this->casesThreshold || $this->deathsThreshold && $record->deaths < $this->deathsThreshold ||
                !empty($this->filterLocations) && !in_array($record->location, $this->filterLocations)) {
                continue;
            }

            if(!in_array($record->location, $this->uniqueLocations)){
                $this->uniqueLocations[] = $record->location;
            }
            if($this->groupByDateAndLocation){
                $data[$record->date][$record->location] = $record;
            } else {
                $data[] = $record;
            }
        }
        if($this->groupByDateAndLocation){
            $this->normalizeData($data);
        }

        return $data;
    }

    private function downloadData()
    {
        if(is_file($this->generateFilename())){
            return json_decode(file_get_contents($this->generateFilename()));
        }

        $data = [];
        $confirmedFile = $this->dataDirectory . '/COVID-19/csse_covid_19_data/csse_covid_19_time_series/time_series_19-covid-Confirmed.csv';
        $deathsFile = $this->dataDirectory . '/COVID-19/csse_covid_19_data/csse_covid_19_time_series/time_series_19-covid-Deaths.csv';

        if (($h = fopen($confirmedFile, "r")) !== FALSE) {
            $header = fgetcsv($h, 1000, ",");
            while (($line = fgetcsv($h, 1000, ",")) !== FALSE) {
                $record = array_combine($header, $line);
                $location = $record['Country/Region'];
                foreach($record as $field => $value){
                    if(preg_match('/^[\d\/]+$/', $field)){
                        $date = (new DateTime($field))->format('Y-m-d');
                        /** @var Data $dataDTO */
                        $dataDTO = isset($data[$location][$date]) ? $data[$location][$date] : new Data($date, $location);
                        $dataDTO->cases += $value;
                        $data[$location][$date] = $dataDTO;
                    }
                }
            }
            fclose($h);
        }

        if (($h = fopen($deathsFile, "r")) !== FALSE) {
            $header = fgetcsv($h, 1000, ",");
            while (($line = fgetcsv($h, 1000, ",")) !== FALSE) {
                $record = array_combine($header, $line);
                $location = $record['Country/Region'];
                foreach($record as $field => $value){
                    if(preg_match('/^[\d\/]+$/', $field)){
                        $date = (new DateTime($field))->format('Y-m-d');
                        /** @var Data $dataDTO */
                        $dataDTO = isset($data[$location][$date]) ? $data[$location][$date] : new Data($date, $location);
                        $dataDTO->deaths += $value;
                        $dataDTO->calcDeathsPercent();
                        $data[$location][$date] = $dataDTO;
                    }
                }
            }
            fclose($h);
        }

        $allData = [];

        foreach($data as $location => $dateData){
            ksort($dateData);
            /** @var Data|null $lastRecord */
            $lastRecord = null;
            foreach($dateData as $date => $record){
                /** @var Data $record */
                if($lastRecord){
                    $record->newCases = $record->cases - $lastRecord->cases;
                    $record->newDeaths = $record->deaths - $lastRecord->deaths;
                } else {
                    $record->newCases = $record->cases;
                    $record->newDeaths = $record->deaths;
                }
                $allData[] = $record;
                $lastRecord = $record;
            }
        }

        file_put_contents($this->generateFilename(), json_encode($allData));

        return $allData;
    }

    private function normalizeData(array &$data)
    {
        $startDate = [];
        if(!empty($this->casesThreshold)){
            ksort($data);
            foreach($data as $date => $locationsData){
                /** @var Data $record */
                foreach($locationsData as $location => $record){
                    if($record->cases >= $this->casesThreshold && empty($startDate[$location])){
                        $startDate[$location] = $date;
                    }
                }
            }

            $normalizedData = [];
            $dayCounter = [];
            foreach($this->filterLocations as $location){
                $dayCounter[$location] = 0;
            }
            foreach($data as $date => $locationsData){
                ksort($locationsData);
                foreach($locationsData as $location=>$record){
                    if(!empty($startDate[$location]) && $date>=$startDate[$location]){
                        $record->date = $dayCounter[$location]++;
                        $normalizedData[$record->date][$location] = $record;
                    }
                }
            }

            $data = $normalizedData;
        }

        foreach($data as $date => $locationsData){
            foreach($this->filterLocations as $location){
                if(empty($locationsData[$location])){
                    $locationsData[$location] = new Data($date, $location);
                }
            }
            ksort($locationsData);
            $data[$date] = $locationsData;
        }
        krsort($data);
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
}