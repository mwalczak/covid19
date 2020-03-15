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
        $includeWorld = $request->get('includeWorld');
        $casesThreshold = $request->get('casesThreshold');
        $deathsThreshold = $request->get('deathsThreshold');

        $data = $dataProvider->getRecentData($includeChina, $includeWorld, $casesThreshold, $deathsThreshold);

        return $this->render('default/index.html.twig', [
            'data' => $data,
            'china' => $includeChina,
            'world' => $includeWorld,
            'cases' => $casesThreshold,
            'deaths' => $deathsThreshold,
            'updated' => $dataProvider->getDataTime()
        ]);
    }
}
