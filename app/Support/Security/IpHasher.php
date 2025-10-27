<?php

namespace App\Support\Security;

use InvalidArgumentException;

class IpHasher
{
    public function __construct(
        private readonly string $algorithm = 'sha256',
        private readonly ?string $salt = null,
    ) {
        if ($this->algorithm === '') {
            throw new InvalidArgumentException('Hash algorithm must not be empty.');
        }

        if (! in_array(strtolower($this->algorithm), array_map('strtolower', hash_algos()), true)) {
            throw new InvalidArgumentException("Hash algorithm [{$this->algorithm}] is not supported.");
        }
    }

    public function hash(?string $ip): ?string
    {
        if ($ip === null) {
            return null;
        }

        $normalized = trim($ip);

        if ($normalized === '') {
            return null;
        }

        $salt = $this->salt ?? '';

        return hash($this->algorithm, $salt.$normalized);
    }
}
