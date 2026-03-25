<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class StartUpController extends AbstractController
{
    /**
     * Redirecciona al inicio del app
     *
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-25
     */
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return new RedirectResponse('/views/index.html');
    }
}
