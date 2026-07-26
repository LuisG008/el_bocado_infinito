<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class SessionService
{
    public function __construct(
        private RequestStack $requestStack
    ) {
    }

    public function getUsuario(): ?array
    {
        return $this->requestStack
            ->getSession()
            ->get('usuario');
    }

    public function getIdUsuario(): ?int
    {
        return $this->getUsuario()['id'] ?? null;
    }

    public function getNombre(): ?string
    {
        return $this->getUsuario()['nombre'] ?? null;
    }

    public function getRoles(): array
    {
        return $this->getUsuario()['roles'] ?? [];
    }

    public function tieneRol(string $rol): bool
    {
        return in_array($rol, $this->getRoles());
    }
}