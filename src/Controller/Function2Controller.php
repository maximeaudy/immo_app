<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class Function2Controller extends AbstractController
{
    /**
     * @Route("/function2", name="function2")
     */
    public function function2(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('budget_max', TextType::class)
            ->add('budget_min', TextType::class)
            ->add('code_postal', TextType::class)
            ->add('code_commune', TextType::class, array('required'=> false))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $task = $form->GetData();
            $collection_name = 'code_postal='.$task['code_postal'];
            $response = $this->get_response($collection_name);
            $this->calculResultat($response, $task['budget_min'], $task['budget_max']);            
            
            return $this->render('function2/function2.html.twig', [
                
            ]);
        }
        return $this->render('function2/task/newfunction2task.html.twig',[
            'form'=>$form->createView()
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

    private function calculResultat ($response, $budget_min, $budget_max)
    {
        $min = 60000;

        for($i=0; $i<$response->{'nb_resultats'}; $i++)
        {
            $temp = ($this->getInfoSurface($response, $i, $budget_min, $budget_max, $terrain, $surface, $totalPos));
                
        }

        if($totalPos==0)
        {
            print("Aucun résultat trouvé pour votre budget");            
        }
        else
        {
            $moyenneTerrain = round($terrain/$totalPos,0);
            $moyenneSurface = round($surface/$totalPos,0);
            print "La surface pour votre budget est d'en moyenne : ".$moyenneSurface."m² avec une surface de terrain de : ".$moyenneTerrain."m²";
        }           
    
    }

    private function getInfoSurface($response, $position, $budget_min, $budget_max, &$terrain, &$surface, &$totalPos){
        $temp = $response->{'resultats'}[$position];
        $surfaceTotal = $temp->{'surface_terrain'} + $temp->{'surface_relle_bati'};
        $valeur_fonciere = $temp->{'valeur_fonciere'};

        if($valeur_fonciere < $budget_min || $valeur_fonciere > $budget_max|| $temp->{'code_type_local'} == 4 || $temp->{'code_type_local'} == null
        || $surfaceTotal == 0 || $surfaceTotal == null || $temp->{'nombre_lots'} > 0 || $temp->{'surface_relle_bati'} == null || 
        $temp->{'surface_relle_bati'} == 0)
            return -1;

        $terrain += $temp->{'surface_terrain'};
        $surface += $temp->{'surface_relle_bati'};
        $totalPos++; 
    }
}