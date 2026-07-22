<?php

namespace sistema\Nucleo;
use PDO;
use PDOException;

class Conexao1
{
    private PDO $pdo;

    public function __construct(
        string $dsn,
        ?string $user,
        ?string $pass,
        ?array $options = []
    ) {
        $this->pdo = new PDO($dsn, $user, $pass, $options);
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}