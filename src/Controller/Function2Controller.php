<?php

namespace App\Controller;

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
        $form = $this->createFormBuilder(null,[
            'constraints' => [
                new Assert\Callback(
                    ['callback' => static function (array $data, ExecutionContextInterface $context){
                        if ($data['code_postal']== null && $data['code_commune']== null){
                            $context
                                ->buildViolation("Veuillez entrer un code postal ou une Ville")
                                ->addViolation()
                            ;
                        }
                    }]
                )
            ]])
            ->add('budget_max', IntegerType::class)
            ->add('budget_min', IntegerType::class)
            ->add('code_postal', IntegerType::class,['required'=> false
                ,'constraints' => [
                    new Assert\Callback(
                        ['callback' => static function ($data, ExecutionContextInterface $context) {
                            if((strlen($data!=0)))
                            {
                                if ((!is_numeric($data)) OR (strlen($data)!=5)) {
                                    $context
                                        ->buildViolation("Veuillez entrer un code postal valide")
                                        ->addViolation()
                                    ;
                                }
                            }
                        }]
                    )
                ]])
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
         if ($response->{'nb_resultats'} > 0)
        {
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
        else
            print("Aucun résultat trouvé pour votre budget"); 
    
    }

    private function getInfoSurface($response, $position, $budget_min, $budget_max, &$terrain, &$surface, &$totalPos){

        $temp = $response->{'resultats'}[$position];
        $surfaceTotal = $temp->{'surface_terrain'} + $temp->{'surface_relle_bati'};
        $valeur_fonciere = $temp->{'valeur_fonciere'};

        if($valeur_fonciere == 0 || $valeur_fonciere < $budget_min || $valeur_fonciere > $budget_max
        || $temp->{'code_type_local'} == 4 || $temp->{'code_type_local'} == null
        || $surfaceTotal == 0 || $surfaceTotal == null || $temp->{'nombre_lots'} > 0 || $temp->{'surface_relle_bati'} == null || 
        $temp->{'surface_relle_bati'} == 0)
            return -1;

        print $valeur_fonciere.'\n';
        $terrain += $temp->{'surface_terrain'};
        $surface += $temp->{'surface_relle_bati'};
        $totalPos++;

    }
}