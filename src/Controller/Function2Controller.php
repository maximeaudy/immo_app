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
                        if ($data['budget_max']<= $data['budget_min']){
                            $context
                                ->buildViolation("Veuillez entrer un budget max supérieur au budget min")
                                ->addViolation()
                            ;
                        }
                    }]
                )
            ]])
            ->add('budget_max', IntegerType::class)
            ->add('budget_min', IntegerType::class)
            ->add('code_postal', IntegerType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $task = $form->GetData();
            $collection_name = 'code_postal='.$task['code_postal'];
            $response = $this->get_response($collection_name);
            $resultat = $this->calculResultat($response, $task['budget_min'], $task['budget_max'], $moyenneSurface, $moyenneTerrain, "1");            
            
            return $this->render('function2/function2.html.twig', [
                'moyenneSurface' => $moyenneSurface,
                'moyenneTerrain' => $moyenneTerrain
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

    private function calculResultat ($response, $budget_min, $budget_max, &$moyenneSurface, &$moyenneTerrain)
    {
         if ($response->{'nb_resultats'} > 0)
        {
            for($i=0; $i<$response->{'nb_resultats'}; $i++)
            {
                $temp = ($this->getInfoSurface($response, $i, $budget_min, $budget_max, $terrain, $surface, $totalPos, $codeLocal));
                    
            }
            if($totalPos==0)
            {
                //function2($request);            
            }
            else
            {
                $moyenneTerrain = round($terrain/$totalPos,0);
                $moyenneSurface = round($surface/$totalPos,0);
            }   
        }            
        else
        {
            $this->addFlash(
                'notice',
                'Vous venez d\'ajouter une intervention'
            );
            return $this->redirectToRoute('function');
        }
    }

    private function getInfoSurface($response, $position, $budget_min, $budget_max, &$terrain, &$surface, &$totalPos, $codeLocal){

        $temp = $response->{'resultats'}[$position];
        $surfaceTotal = $temp->{'surface_terrain'} + $temp->{'surface_relle_bati'};
        $valeur_fonciere = $temp->{'valeur_fonciere'};

        if($valeur_fonciere < 100 || $valeur_fonciere < $budget_min || $valeur_fonciere > $budget_max
        || $temp->{'code_type_local'} != $codeLocal || $temp->{'code_type_local'} == null
        || $surfaceTotal == 0 || $surfaceTotal == null || $temp->{'nombre_lots'} > 0 || $temp->{'surface_relle_bati'} == null || 
        $temp->{'surface_relle_bati'} == 0)
            return -1;

        $terrain += $temp->{'surface_terrain'};
        $surface += $temp->{'surface_relle_bati'};
        $totalPos++;
    }
}