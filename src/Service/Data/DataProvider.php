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

    public function __construct(string $dataDirectory, LoggerInterface $logger)
    {
        $this->dataDirectory = $dataDirectory;
        $this->logger = $logger;
    }

    public function getRecentData(?bool $includeChina = false, ?bool $includeWorld = false, ?bool $casesThreshold = false, ?bool $deathsThreshold = false)
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
                if ($record->isChina && !$includeChina || $record->isWorld && !$includeWorld || $casesThreshold && $record->cases < 100 || $deathsThreshold && $record->deaths < 100) {
                    continue;
                }
                $data[] = $record;
            }
            fclose($h);
        }

        return $data;
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