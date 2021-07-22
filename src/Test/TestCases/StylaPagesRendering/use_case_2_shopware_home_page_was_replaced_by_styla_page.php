<?php

use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

$expectedGuzzleRequest = new GuzzleRequest(
    'GET',
    'https://seoapi.styla.com/clients/foo_account?url='
);

$body = <<<JSON
        {
            "tags": [],
            "html": {
                "head": "<title>Styla test page</title><meta name=\"description\" content=\"Styla test page description\">",
                "body": "Styla page test content"
            }
        }
JSON;

$guzzleResponse = new GuzzleResponse(
    200,
    ['Content-Type' => 'application/json'],
    $body
);

return [
    $expectedGuzzleRequest,
    $guzzleResponse,
    '',
    [
        '<script type="text/javascript" src="https://engine.styla.com/init.js" async></script>',
        '<title>Styla test page</title><meta name="description" content="Styla test page description">',
        'Styla page test content'
    ]
];
