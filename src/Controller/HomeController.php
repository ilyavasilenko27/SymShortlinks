<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * Главная страница
     *
     * @param Request $oRequest
     * @param ManagerRegistry $oDoctrine
     *
     * @return Response
     */
    #[Route('/', name: 'home')]
    public function renderPage(Request $oRequest, ManagerRegistry $oDoctrine): Response
    {
        return $this->render(
            'home.html.twig',
            []
        );
    }
}