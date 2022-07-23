<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClassTestController extends AbstractController
{
    #[Route('/class/test', name: 'app_class_test')]
    public function index(): Response
    {
        return $this->render('class_test/index.html.twig', [
            'controller_name' => 'ClassTestController',
        ]);
    }
}
