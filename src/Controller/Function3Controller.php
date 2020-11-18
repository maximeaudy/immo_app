<?php
// TODO : position automatique
namespace App\Controller;

use Doctrine\Common\Collections\Expr\Value;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class Function3Controller extends AbstractController {
    /**
     * @Route("/pres-de-chez-moi", name="function3")
     */
    public function function3(Request $request)
    { 
        //Initialisation des données de test
        $latitude = '44.711836';
        $longitude = '-0.480107';
        $dist = '100';

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

            //Vérifications des données
            $indice = 0;
            foreach ($responseDecode as $feature) {
                if (!array_key_exists('numero_voie', $feature['properties'])) {
                    $responseDecode[$indice]['properties']['numero_voie'] = "";
                }
                if (!array_key_exists('surface_relle_bati', $feature['properties'])) {
                    $responseDecode[$indice]['properties']['surface_relle_bati'] = $feature['properties']['surface_terrain'];
                }
                if (!array_key_exists('nombre_pieces_principales', $feature['properties'])) {
                    $responseDecode[$indice]['properties']['nombre_pieces_principales'] = 'Aucune';
                }
                $indice++;
            }

            //Render
            return $this->render('function3/function3Recherche.html.twig', [
                'form' => $form->createView(),
                'response' => $responseDecode,
                'error' => $error
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