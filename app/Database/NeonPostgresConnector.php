<?php

namespace App\Database;

use Illuminate\Database\Connectors\PostgresConnector;
use InvalidArgumentException;

class NeonPostgresConnector extends PostgresConnector
{
    protected function getDsn(array $config)
    {
        $dsn = parent::getDsn($config);
        $endpoint = $config['neon_endpoint'] ?? null;

        if (! $endpoint) {
            return $dsn;
        }

        if (! preg_match('/^ep-[a-z0-9-]+$/', $endpoint)) {
            throw new InvalidArgumentException('Identificador de endpoint Neon invalido.');
        }

        return $dsn.";options='endpoint={$endpoint}'";
    }
}
