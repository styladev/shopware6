<?php

namespace Styla\CmsIntegration\Styla\Client\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class ClientFactory
{
    public function create(): ClientInterface
    {
        return new Client();
    }
}
