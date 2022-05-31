<?php

declare(strict_types=1);

namespace App\Client;

use http\Client\Response;
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
        private HttpClientInterface $httpClient,
        string                      $env
    )
    {
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

        $response = $this->httpClient->request('GET', $endpoint, [
            'headers' => [
                'User-Agent' => 'Auction X client'
            ]
        ]);
        $content = $response->toArray(false);

        if ($response->getStatusCode() !== 200) {
            throw new ImmutableXClientException($content['message']);
        }

        return $content;
    }
}