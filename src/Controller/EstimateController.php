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
     private function array_median($arr) {
        if(empty($arr)){
            return false;
        }
        $num = count($arr);
        $middleVal = floor(($num - 1) / 2);
        if($num % 2) {
            return $arr[$middleVal];
        }
        else {
            $lowMid = $arr[$middleVal];
            $highMid = $arr[$middleVal + 1];
            return (($lowMid + $highMid) / 2);
        }
    }

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

            $delta = null;
            if($type == 1) {
                $delta = 10;
            } elseif($type == 2) {
                $delta = 2;
            }

            $total_property = 0;
            $total_property2018 = 0;
            $total_property2019 = 0;
            $tab_all_price = [];
            $tab_2018_all_price = [];
            $tab_2019_all_price = [];
            $price_average_2018 = 0;
            $price_average_2019 = 0;

            foreach ($response['resultats'] as $propertySale){
                $date = explode("-", $propertySale['date_mutation']);
                if(($propertySale['surface_relle_bati'] <= $area + $delta || is_null($area)) &&
                    ($propertySale['surface_relle_bati'] >= $area - $delta || is_null($area)) &&
                    ((int)$date[0] >= (int)"2018") &&
                    ($propertySale['surface_terrain'] == $surface || is_null($surface)) &&
                    ($propertySale['nombre_pieces_principales'] == $numberRoom || is_null($numberRoom))&&
                    ($propertySale['code_type_local'] == $type || is_null($type)) &&
                    ($propertySale['section'] == $section || is_null($section))) {
                    $total_property++;
                    $tab_all_price[] = $propertySale['valeur_fonciere'];
                    if ($date[0] === "2018")  {
                        $total_property2018++;
                        $tab_2018_all_price[] = $propertySale['valeur_fonciere'];
                    } else if ($date[0] === "2019") {
                        $total_property2019++;
                        $tab_2019_all_price[] = $propertySale['valeur_fonciere'];
                    }
                }
            }

            $augmentation = null;
            if (!empty($tab_2018_all_price) && !empty($tab_2019_all_price)) {
                sort($tab_2018_all_price);
                $price_average_2018 = $this->array_median($tab_2018_all_price);
                sort($tab_2019_all_price);
                $price_average_2019 = $this->array_median($tab_2019_all_price);
                $augmentation = $price_average_2019 / $price_average_2018;
            }


            if($total_property == 0){
                var_dump('aucun resultat correspondant à votre recherche');die;
            }
            sort($tab_all_price);
            $price = $this->array_median($tab_all_price);

            if ($augmentation != null) {
                $estimation_2_years_later = $price * $augmentation;
            }

            if($modern){
                $price = $price * 1.10;
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
            'estimatePrice' => $price ?? null,
            'estimatePrice2years' => $estimation_2_years_later ?? null,
        ]);
    }
}
