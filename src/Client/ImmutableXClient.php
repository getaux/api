<?php

declare(strict_types=1);

namespace App\Client;

use GuzzleHttp\Client;

class ImmutableXClient
{
    public const ENV_DEV = 'dev';
    public const ENV_PROD = 'prod';

    private const ALLOWED_ENVS = [
        self::ENV_DEV,
        self::ENV_PROD,
    ];

    private string $env = 'dev';
    private Client $httpClient;

    public function __construct(string $env)
    {
        $this->httpClient = new Client();
        $this->setEnvironment($env);
    }

    private function getDomain(): string
    {
        return 'https://api.' . ($this->env === 'dev' ? 'ropsten.' : '') . 'x.immutable.com/';
    }

    public function setEnvironment(string $env): void
    {
        if (in_array($env, self::ALLOWED_ENVS)) {
            $this->env = $env;
        }
    }

    /** @todo manage errors & rate limit */
    public function get(string $method, array $parameters = []): array
    {
        $endpoint = $this->getDomain() . $method;

        foreach ($parameters as $key => $value) {
            $endpoint .= ($key === array_key_first($parameters) ? '?' : '&') . $key . '=' . $value;
        }

        $response = $this->httpClient->request('GET', $endpoint);
        return (array)json_decode((string)$response->getBody(), true);
    }
}