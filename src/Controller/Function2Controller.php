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
            ->add('budget', TextType::class)
            ->add('code_postal', TextType::class)
            ->add('code_commune', TextType::class, array('required'=> false))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $collection_name = 'numero_plan=94068000CQ0110';
            $response = $this->get_response($collection_name);
            $task = $form->GetData();


            print $this->prix_metre_carre($response);
            return $this->render('function2/function2.html.twig', [
                'task' => $task
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