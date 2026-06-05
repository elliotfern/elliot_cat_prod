<?php

declare(strict_types=1);

namespace App\Application\Usuari\UseCase;

use App\Domain\Usuari\Repository\UsuariRepository;
use App\Application\Security\PasswordHasher;
use App\Infrastructure\Security\Jwt\JwtService;
use App\Domain\Usuari\ValueObject\Email;

final class LoginUsuariUseCase
{
    public function __construct(
        private UsuariRepository $repository,
        private PasswordHasher $passwordHasher,
        private JwtService $jwtService
    ) {}

    public function execute(string $email, string $password): array
    {


        // 1. buscar usuari
        $usuari = $this->repository->findByEmail(
            new Email($email)
        );

        if (!$usuari) {
            throw new \RuntimeException('User not found');
        }



        // 2. comprovar estat
        if (!$usuari->isActive() || $usuari->isDeleted()) {
            throw new \RuntimeException('User inactive or deleted');
        }

        // 3. verificar password
        if (!$usuari->password()->verify($password)) {
            throw new \RuntimeException('Invalid credentials');
        }

        // 4. payload JWT (equivalent legacy)
        $payload = [
            'user_id' => $usuari->id()->toString(),
            'email' => $usuari->email()->value(),
            'full_name' => $usuari->nom() . ' ' . $usuari->cognom(),
            'role' => $usuari->role()->value,
            'iat' => time(),
            'exp' => time() + 604800, // 7 dies
        ];

        $token = $this->jwtService->encode($payload);

        return [
            'token' => $token,
            'user' => [
                'id' => $usuari->id()->toString(),
                'email' => $usuari->email()->value(),
                'full_name' => $usuari->nom() . ' ' . $usuari->cognom(),
                'role' => $usuari->role()->value,
            ]
        ];
    }
}
