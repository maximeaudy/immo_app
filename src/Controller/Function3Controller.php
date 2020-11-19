<?php
// TODO : position automatique
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Phpml\Regression\SVR;
use Phpml\SupportVectorMachine\Kernel;

class Function3Controller extends AbstractController {
    /**
     * @Route("/pres-de-chez-moi", name="pres-de-chez-moi")
     */
    public function function3(Request $request)
    { 
        $error = "";

        //Création du formulaire
        $form = $this->createFormBuilder()
            ->add('latitude', NumberType::class, array('required' => true))
            ->add('longitude', NumberType::class, array('required' => true))
            ->add('distance', NumberType::class, ['attr' => ['min' => 1, 'max' => 1000]])
            ->getForm();

        $form->handleRequest($request);

        //Lorsque le formulaire est envoyé
        if ($form->isSubmitted() && $form->isValid()) {

            //Récupération des données saisies
            $latitude = $form->get('latitude')->getData();
            $longitude = $form->get('longitude')->getData();
            $dist = $form->get('distance')->getData()/10;

            //Appel API
            $response = $this->askAPI($dist, $latitude, $longitude);
            $responseDecode = json_decode($response, TRUE)['features'];

            if (empty($responseDecode)) {
                $error = "Aucune correspondance trouvée";
            }

            $prixMetreCarreArray = [];

            //Vérifications des données
            $indice = 0;
            foreach ($responseDecode as $feature) {
                if (!array_key_exists('numero_voie', $feature['properties'])) {
                    $responseDecode[$indice]['properties']['numero_voie'] = "";
                }
                if (!array_key_exists('surface_relle_bati', $feature['properties'])) {
                    if (array_key_exists('surface_terrain', $feature['properties'])) {
                        $responseDecode[$indice]['properties']['surface_relle_bati'] = $feature['properties']['surface_terrain'];
                    } else if (array_key_exists('surface_lot_1', $feature['properties'])) {
                        $responseDecode[$indice]['properties']['surface_relle_bati'] = $feature['properties']['surface_lot_1'];
                    } else {
                        $responseDecode[$indice]['properties']['surface_relle_bati'] = '0';
                    }
                }
                if (!array_key_exists('nombre_pieces_principales', $feature['properties'])) {
                    $responseDecode[$indice]['properties']['nombre_pieces_principales'] = 'Aucune';
                }
                if (!array_key_exists('type_voie', $feature['properties'])) {
                    $responseDecode[$indice]['properties']['type_voie'] = '';
                }
                if (!array_key_exists('voie', $feature['properties'])) {
                    $responseDecode[$indice]['properties']['voie'] = '';
                }
                if (!array_key_exists('code_postal', $feature['properties'])) {
                    $responseDecode[$indice]['properties']['code_postal'] = '';
                }

                if($responseDecode[$indice]['properties']['surface_relle_bati'] != 0 && array_key_exists('valeur_fonciere', $feature['properties']) && $responseDecode[$indice]['properties']['valeur_fonciere'] != 0) {
                    $prixMetreCarreArray[$indice]=$responseDecode[$indice]['properties']['valeur_fonciere']/$responseDecode[$indice]['properties']['surface_relle_bati'];
                }

                if (!array_key_exists('valeur_fonciere', $feature['properties'])) {
                    $responseDecode[$indice]['properties']['valeur_fonciere'] = '0';
                }

                $indice++;
            }

            $prixMetreCarreArraySum=array_sum($prixMetreCarreArray);

            //Render
            return $this->render('function3/function3Recherche.html.twig', [
                'form' => $form->createView(),
                'response' => $responseDecode,
                'error' => $error,
                'estimationPrixCarre' => round($prixMetreCarreArraySum)
            ]);
        }

        return $this->render('function3/function3.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function askAPI($dist, $latitude, $longitude) {
        $url = 'http://api.cquest.org/dvf';
        $collection_name= 'dist='.$dist.'&lat='.$latitude.'&lon='.$longitude;
        $request_url= $url.'?'.$collection_name;
        $curl = curl_init($request_url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}