<?php

namespace App\Controller;

use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Type\Function2Type;

class Function2Controller extends AbstractController
{
    /**
     * @Route("/function2", name="function2")
     */
    public function function2()
    {

        $collection_name= 'numero_plan=94068000CQ0110';
        $response = $this->get_response($collection_name);

        print $this->prix_metre_carre($response);
        return $this->render('function2/function2.html.twig', [

        ]);
    }

    private function get_response($collection_name){
        $url = 'http://api.cquest.org/dvf';

        $request_url= $url.'?'.$collection_name;
        $curl = curl_init($request_url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response= json_decode($response);

    }

    private function prix_metre_carre($response){
        $surface = $response->{'resultats'}[0]->{'surface_terrain'};
        $valeur_fonciere = $response->{'resultats'}[0]->{'valeur_fonciere'};
        if ($surface == 0){
            $surface = $surface = $response->{'resultats'}[0]->{'surface_relle_bati'};
        }
        $prix_metre_carre = round($valeur_fonciere/$surface,3);
        return $prix_metre_carre;
    }
}