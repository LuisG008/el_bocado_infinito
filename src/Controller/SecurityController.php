<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use Symfony\Component\HttpFoundation\RedirectResponse;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        

        //return $this->render('/views/login/login.html', ['last_username' => $lastUsername, 'error' => $error]);
        //dd($lastUsername, $error);
        if($error){
            throw new InvalidArgumentException($error->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        if(!$lastUsername) {
            return new JsonResponse([
                'message' => 'Session finalizada',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return new RedirectResponse('/views/login/login.html');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
