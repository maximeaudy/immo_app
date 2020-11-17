<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class Function2Controller extends AbstractController
{
    /**
     * @Route("/function2", name="function2")
     */
    public function function2()
    {
        $url = 'http://api.cquest.org/dvf';
        $collection_name= 'numero_plan=94068000CQ0110';
        $request_url= $url.'?'.$collection_name;
        $curl = curl_init($request_url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);
        echo var_dump($response->{'valeur_fonciere'});
        return $this->render('function2/function2.html.twig', [

        ]);
    }
    private function random()
    {
        return random_int(0, 100);
    }
}