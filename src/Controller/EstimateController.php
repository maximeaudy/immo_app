<?php

namespace App\Controller;

use App\Form\EstimatePriceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EstimateController extends AbstractController
{
    /**
     * @Route("/estimate", name="estimate")
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(EstimatePriceType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $cityCode = $data['cityCode'];
            $surface = $data['surface'];
            $area = $data['area'];
            $numberRoom = $data['numberRoom'];
            $type = $data['type'];
            $modern = $data['modern'];
            $transport = $data['transport'];
            $shops = $data['shops'];
            $section = $data['section'];
            $travaux = $data['travaux'];

            if($type == 1){
                $typeString = "Maison";
            }elseif($type == 2){
                $typeString = "Appartement";
            }elseif($type == 3){
                $typeString = "Dépendance";
            }elseif ($type == 4) {
                $typeString = "Local industriel. commercial ou assimilé";
            }else{
                $typeString = "Appartement";
            }

            try {
                $httpClient = HttpClient::create();
                $response = $httpClient->request(
                    'GET',
                    'http://api.cquest.org/dvf?nature_mutation=Vente&type_local='. $typeString .'&code_postal=' . $cityCode
                )->getContent();
            }catch (\Exception $e){
                var_dump($e->getMessage());
            }

            $response = json_decode($response, true);

            if(is_null($response['resultats'])){
                var_dump('aucun resultat');die;
            }

            $total_property = 0;
            $total_price = 0;
            $total_property2014 = 0;
            $total_price2014 = 0;
            $total_property2018 = 0;
            $total_price2018 = 0;
            $price_average_2014 = 0;
            $price_average_2018 = 0;
            foreach ($response['resultats'] as $propertySale){
                if(($propertySale['surface_relle_bati'] == $area || is_null($area)) &&
                    ($propertySale['surface_terrain'] == $surface || is_null($surface)) &&
                    ($propertySale['nombre_pieces_principales'] == $numberRoom || is_null($numberRoom))&&
                    ($propertySale['code_type_local'] == $type || is_null($type)) &&
                    ($propertySale['section'] == $section || is_null($section))) {
                    $total_property++;
                    $total_price += $propertySale['valeur_fonciere'];
                    $date = explode("-", $propertySale['date_mutation']);
                    if ($date[0] === "2014")  {
                        $total_property2014++;
                        $total_price2014 += $propertySale['valeur_fonciere'];
                        $price_average_2014 = round($total_price2014 / $total_property2014, 0);;
                    } else if ($date[0] === "2018") {
                        $total_property2018++;
                        $total_price2018 += $propertySale['valeur_fonciere'];
                        $price_average_2018 = round($total_price2018 / $total_property2018, 0);;
                    }
                }
            }
            $augmentation = $price_average_2018 / $price_average_2014;

            $estimation_2022 = $price_average_2018 * $augmentation;


            if($total_property == 0){
                var_dump('aucun resultat correspondant à votre recherche');die;
            }

            $price = round($total_price / $total_property, 0);
            if($modern){
                $price = $price * 1.15;
            }
            if($transport){
                $price = $price * 1.03;
            }
            if($shops){
                $price = $price * 1.01;
            }
            if($travaux){
                $price = $price * 0.90;
            }
        }


        return $this->render('estimate/index.html.twig', [
            'estimatePriceForm' => $form->createView(),
            'estimatePrice' => $price ?? null
        ]);
    }
}
