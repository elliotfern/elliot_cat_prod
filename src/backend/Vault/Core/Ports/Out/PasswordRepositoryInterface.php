<?php

namespace App\Vault\Core\Ports\Out;

interface PasswordRepositoryInterface
{
    public function getPasswords(): array;
    public function getPasswordDesencrypt(int $serviceId): array;
    public function getClau2FDesencrypt(int $serviceId): array;
    public function savePassword(int $userId, string $serviceName, string $password, string $type, string $url): void;
}
