<?php

namespace App\Controller;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class Function2Controller extends AbstractController
{
    /**
     * @Route("/function2", name="function2")
     */
    public function function2(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('budget', IntegerType::class)
            ->add('code_postal', IntegerType::class)
            ->add('type',ChoiceType::class,[
                'choices' =>[
                    'Appartement' => '2',
                    'Maison' => '1'
                ]
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $task = $form->GetData();
            $collection_name = 'code_postal='.$task['code_postal'];
            $response = $this->get_response($collection_name, $task['type']);
            $resultat = $this->calculResultat($response, $task['budget'], $surface, $terrain, $surfaceMax, $terrainMax, $task['type']);            
            if($resultat == -1){
                $this->addFlash(
                    'notice',
                    'Aucun resultat trouvé'
                );
                return $this->redirectToRoute('function2');

            }
            return $this->render('function2/function2.html.twig', [
                'type' => $task['type'],
                'surface' => $surface,
                'terrain' => $terrain,
                'surfaceMax' => $surfaceMax,
                'terrainMax' => $terrainMax
            ]);
        }
        return $this->render('function2/task/newfunction2task.html.twig',[
            'form'=>$form->createView()
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

    private function calculResultat ($response, $budget, &$surface, &$terrain, &$surfaceMax, &$terrainMax, $codeLocal)
    {
        $surface = 0;
         if ($response->{'nb_resultats'} > 0)
        {      
            if ($codeLocal == "2")
            {
                $this->getInfoSurfaceAppt($response, $budget, $surface);
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

            if( $valeur_fonciere < $budget  && $temp->{'nombre_lots'} == 0 && $temp->{'surface_relle_bati'} > 0)
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

    private function getInfoSurfaceAppt($response, $budget, &$surface){

        for($position=0; $position < $response->{'nb_resultats'}; $position++)
        {
            $temp = $response->{'resultats'}[$position];
            $surfaceTotal = $temp->{'surface_relle_bati'};
            $valeur_fonciere = $temp->{'valeur_fonciere'};

            if($valeur_fonciere < $budget  && $surfaceTotal > 0 && $temp->{'nombre_lots'} == 0
            && $surfaceTotal > $surface)
            {
                $surface = $surfaceTotal;
            }            
        }
    }
}