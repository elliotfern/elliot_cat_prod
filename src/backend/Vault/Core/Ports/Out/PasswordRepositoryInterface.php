<?php

namespace App\Vault\Core\Ports\Out;

interface PasswordRepositoryInterface
{
    public function getPasswords(): array;
    public function getPasswordDesencrypt(string $serviceId): array;
    public function getClau2FDesencrypt(string $serviceId): array;
    public function savePassword(int $userId, string $serviceName, string $password, string $type, string $url): void;
}
