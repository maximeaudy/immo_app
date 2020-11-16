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

        $number = $this->random();
        return $this->render('function2/function2.html.twig', [
            'number' => $number,
        ]);
    }
    private function random()
    {
        return random_int(0, 100);
    }
}