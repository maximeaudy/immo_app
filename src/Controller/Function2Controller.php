<?php

namespace App\Controller;

use App\Form\Type\Function2Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class Function2Controller extends AbstractController
{
    const MIN_BUDGET = 5000;
    /**
     * @Route("/function2", name="function2")
     */
    public function function2(Request $request)
    {
        $form = $this->createForm(Function2Type::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $task = $form->GetData();
            $collection_name = 'code_postal='.$task['code_postal'];
            $response = $this->get_response($collection_name, $task['type']);
            $resultat = $this->calculResultat($response, $task['budget'], $surface, $terrain, $surfaceMax, $terrainMax, $task['type'], $nbpiece);            
            if($resultat == -1){
                $this->addFlash(
                    'notice',
                    'Aucun resultat trouvé'
                );
                $task = null;
            }
            if($surface == $surfaceMax){
                $surface = null;
                $terrainMax = null;
            }
        }
        return $this->render('function2/function2.html.twig', [
            'type' => $task['type'] ?? null ,
            'surface' => $surface ?? null,
            'terrain' => $terrain ?? null,
            'surfaceMax' => $surfaceMax ?? null,
            'terrainMax' => $terrainMax ?? null,
            'nbpiece' => $nbpiece ?? null,
            'form' => $form->createView()
        ]);
    }

    private function get_response($collection_name, $type){
        $url = 'http://api.cquest.org/dvf';

        if ($type == "1") 
            $collection_name = $collection_name.'&type_local=Maison';
        else
            $collection_name = $collection_name.'&type_local=Appartement';

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

    private function calculResultat ($response, $budget, &$surface, &$terrain, &$surfaceMax, &$terrainMax, $codeLocal, &$nbpiece)
    {
        $surface = 0;
         if ($response->{'nb_resultats'} > 0)
        {      
            if ($codeLocal == "2")
            {
                $this->getInfoSurfaceAppt($response, $budget, $surface, $nbpiece);
                if($surface == 0)
                {
                    return -1;
                }
                return 1;
            }
            else 
            {
                $this->getInfoSurfaceMaison($response, $budget, $terrainMax, $surface, $surfaceMax, $terrain);
                if($surfaceMax == 0 && $terrainMax == 0)
                    return -1;
                return 1;
            } 
        }            
        else
        {
            return -1;
        }
    }

    private function getInfoSurfaceMaison($response, $budget, &$terrainMax, &$surface, &$surfaceMax, &$terrain){

        $terrainMax = 0;
        $surfaceMax = 0;

        for($position=0; $position < $response->{'nb_resultats'}; $position++)
        {
            $temp = $response->{'resultats'}[$position];
            $surfaceTotal = $temp->{'surface_terrain'} + $temp->{'surface_relle_bati'};
            $valeur_fonciere = $temp->{'valeur_fonciere'};
            
            if( $valeur_fonciere > self::MIN_BUDGET && $valeur_fonciere < $budget  && $temp->{'nombre_lots'} == 0 && $temp->{'surface_relle_bati'} > 0 && str_starts_with($temp->{'date_mutation'},'2019') or  str_starts_with($temp->{'date_mutation'},'2020'))

            {
                $terrainTmp = $temp->{'surface_terrain'};
                $surfaceTmp = $temp->{'surface_relle_bati'};

                if ($surfaceTmp > $surfaceMax )
                {
                    $surfaceMax = $surfaceTmp;
                    $terrain = $terrainTmp;
                }
                if ($terrainTmp > $terrainMax)
                {
                    $terrainMax = $terrainTmp;
                    $surface = $surfaceTmp;
                }
            }            
        }
    }

    private function getInfoSurfaceAppt($response, $budget, &$surface, &$nbpiece){

        for($position=0; $position < $response->{'nb_resultats'}; $position++)
        {
            $temp = $response->{'resultats'}[$position];
            $surfaceTotal = $temp->{'surface_relle_bati'};
            $valeur_fonciere = $temp->{'valeur_fonciere'};
            $tempPiece = $temp->{'nombre_pieces_principales'};

            if($tempPiece > 0 && $valeur_fonciere > self::MIN_BUDGET && $valeur_fonciere < $budget  && $surfaceTotal > 0 && $temp->{'nombre_lots'} == 0 && $surfaceTotal > $surface)
            {
                $surface = $surfaceTotal;
                $nbpiece = $temp->{'nombre_pieces_principales'};
            }            
        }
    }
}