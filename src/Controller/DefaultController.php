<?php

namespace App\Controller;

use App\Service\Data\DataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(DataProvider $dataProvider, Request $request)
    {
        $includeChina = $request->get('includeChina');
        $casesThreshold = $request->get('casesThreshold', 10000);
        $deathsThreshold = $request->get('deathsThreshold', 1000);

        $dataProvider
            ->setChina((bool) $includeChina);
        if($casesThreshold){
            $dataProvider->setCasesThreshold($casesThreshold);
        }
        if($deathsThreshold){
            $dataProvider->setDeathsThreshold($deathsThreshold);
        }
        $data = $dataProvider->getRecentData();

        return $this->render('default/index.html.twig', [
            'data' => $data,
            'china' => $includeChina,
            'cases' => $casesThreshold,
            'deaths' => $deathsThreshold,
            'updated' => $dataProvider->getDataTime(),
            'locationsCount' => $dataProvider->getLocationsCount()
        ]);
    }

    /**
     * @Route("/compare", name="compare")
     */
    public function compare(DataProvider $dataProvider, Request $request)
    {
        $locations = explode(',', $request->get('locations'));
        $casesThreshold = $request->get('casesThreshold', 0);
        $chart = $request->get('chart');

        $dataProvider
            ->filterLocations($locations)
            ->groupByNameAndLocation();
        if($casesThreshold){
            $dataProvider->setCasesThreshold($casesThreshold);
        }

        $data = $dataProvider->getRecentData();

        krsort($data);

        sort($locations);

        return $this->render('default/compare.html.twig', [
            'data' => $data,
            'locations' => $locations,
            'updated' => $dataProvider->getDataTime(),
            'reverse' => !(bool) $casesThreshold,
            'casesThreshold' => $casesThreshold,
            'chart' => $chart
        ]);
    }
}
