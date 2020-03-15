<?php

namespace App\Controller;

use App\Service\Data\DataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/{includeChina?}/{includeWorld?}/{casesThreshold?}/{deathsThreshold?}", name="index")
     */
    public function index($includeChina, $includeWorld, $casesThreshold, $deathsThreshold, DataProvider $dataProvider, Request $request)
    {
        $includeChina ??= 0;
        $includeWorld ??= 0;
        $casesThreshold ??= 0;
        $deathsThreshold ??= 0;

        $data = $dataProvider->getRecentData((bool) $includeChina, (bool) $includeWorld, (bool) $casesThreshold, (bool) $deathsThreshold);

        return $this->render('default/index.html.twig', [
            'data' => $data,
            'china' => $includeChina,
            'world' => $includeWorld,
            'cases' => $casesThreshold,
            'deaths' => $deathsThreshold
        ]);
    }
}
