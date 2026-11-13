<?php

namespace Styla\CmsIntegration\Styla\Client\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class ClientFactory
{
    public const CONNECT_TIMEOUT_SECONDS = 3;
    public const TIMEOUT_SECONDS = 10;

    public function create(): ClientInterface
    {
        // The Styla APIs are called on the storefront request path (page details), so a slow API must not
        // tie up PHP workers indefinitely
        return new Client([
            'connect_timeout' => self::CONNECT_TIMEOUT_SECONDS,
            'timeout' => self::TIMEOUT_SECONDS,
        ]);
    }
}
