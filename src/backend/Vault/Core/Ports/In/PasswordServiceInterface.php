<?php

namespace App\Vault\Core\Ports\In;

interface PasswordServiceInterface
{
    public function getPasswords(): array;
    public function getPasswordDesencrypt(int $serviceId): array;
    public function savePassword(int $userId, string $serviceName, string $password, string $type, string $url): void;
}
