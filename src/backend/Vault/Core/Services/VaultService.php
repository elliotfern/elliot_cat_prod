<?php

namespace App\Vault\Core\Services;

use App\Vault\Core\Ports\In\PasswordServiceInterface;
use App\Vault\Core\Ports\Out\PasswordRepositoryInterface;

class VaultService implements PasswordServiceInterface
{
    private $passwordRepository;

    // Aquí inyectamos el repositorio que usaremos para interactuar con la base de datos
    public function __construct(PasswordRepositoryInterface $passwordRepository)
    {
        $this->passwordRepository = $passwordRepository;
    }

    // Método que obtiene las contraseñas desde el repositorio
    public function getPasswords(): array
    {
        return $this->passwordRepository->getPasswords();
    }

    // Método que obtiene una contraseña y la desencripta
    public function getPasswordDesencrypt(int $serviceId): array
    {
        return $this->passwordRepository->getPasswordDesencrypt($serviceId);
    }

    // Método que obtiene una contraseña y la desencripta
    public function getClau2FDesencrypt(int $serviceId): array
    {
        return $this->passwordRepository->getClau2FDesencrypt($serviceId);
    }


    // Método que guarda una nueva contraseña en el repositorio
    public function savePassword(int $userId, string $serviceName, string $password, string $type, string $url): void
    {
        $this->passwordRepository->savePassword($userId, $serviceName, $password, $type, $url);
    }
}
