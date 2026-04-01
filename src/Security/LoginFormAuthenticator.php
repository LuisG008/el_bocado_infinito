<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

use App\Repository\UsuarioRepository;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use App\Service\UsuarioService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UsuarioRepository $usuarioRepository
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $identificacion = $request->getPayload()->getString('identificacion');
        
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $identificacion);

        return new Passport(
            new UserBadge($identificacion, function ($identificacion) {
            $user = $this->usuarioRepository->findOneBy([
                'identificacion' => $identificacion
            ]);

            if (!$user) {
                throw new CustomUserMessageAuthenticationException('Usuario no encontrado');
            }

            if ($user->getEstado() !== UsuarioService::ESTADO_ACTIVO) {
                throw new CustomUserMessageAuthenticationException('Usuario inactivo');
            }

            return $user;
        }),
            new PasswordCredentials($request->getPayload()->getString('password'))
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        //dd($request, $token, $firewallName, $this->getTargetPath($request->getSession(), $firewallName));
        /*if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }*/
        $data = [
            'message' => 'Login successful'
        ];

        return new JsonResponse($data, $status = Response::HTTP_OK);
            
        // For example:
        //return new RedirectResponse('/views/dashboard/dashboard.html');
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return new JsonResponse([
            'error' => 'No autenticado',
            'detail' => $exception->getMessage()
        ], Response::HTTP_UNAUTHORIZED);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        // Si es AJAX
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'message' => 'Sesión finalizada'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Si es navegador normal
        return new RedirectResponse('/views/login/login.html');
    }
}
