<?php

declare(strict_types=1);

namespace App\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImmutableXClient
{
    public const ENV_DEV = 'dev';
    public const ENV_PROD = 'prod';

    private const ALLOWED_ENVS = [
        self::ENV_DEV,
        self::ENV_PROD,
    ];

    private string $env = 'dev';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        string                               $env
    ) {
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

    public function get(string $method, array $parameters = [], bool $withRetry = true): array
    {
        $endpoint = $this->getDomain() . $method;

        foreach ($parameters as $key => $value) {
            $endpoint .= ($key === array_key_first($parameters) ? '?' : '&') . $key . '=' . $value;
        }

        $response = $this->httpClient->request('GET', $endpoint, [
            'headers' => [
                'User-Agent' => 'AuctionX client'
            ]
        ]);
        $content = $response->toArray(false);

        if ($response->getStatusCode() !== 200) {
            if ($withRetry === true) {
                usleep(1000000);
                $this->get($method, $parameters, false);
            }

            throw new ImmutableXClientException(
                sprintf('IMX API Error: %s', $content['message']),
                $response->getStatusCode()
            );
        }

        return $content;
    }
}
