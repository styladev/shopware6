<?php

use GuzzleHttp\Psr7\Response;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaPagesSynchronization;
use Styla\CmsIntegration\Test\Constraint\StylaPageMatchConstraint;
use Styla\CmsIntegration\Test\Constraint\StylaPagesMatchConstraint;
use Styla\CmsIntegration\Test\Constraint\StylaSynchronizationMatchConstraint;
use Styla\CmsIntegration\Test\DataFixtures\LoadStylaPages;

$expectedFooAccountApiResponseBody = <<<JSON
[
  {
    "name": "page/test-st",
    "domain": "https://example.account.com",
    "path": "/page/test-st",
    "type": "PAGE",
    "updatedAt": 1624475094,
    "deletedAt": null,
    "position": null,
    "title": "test st",
    "seoTitle": "test st"
  },
  {
    "name": "page/bar-test-page",
    "domain": "https://example.account.com",
    "path": "/page/bar-page",
    "type": "PAGE",
    "updatedAt": 1624457941,
    "deletedAt": 1624475094,
    "position": null,
    "title": "Bar test page",
    "seoTitle": "Seo Bar test page"
  },
  {
    "name": "Qux page",
    "domain": "https://example.account.com",
    "path": "/page/qux-page",
    "type": "PAGE",
    "updatedAt": 1549065600,
    "deletedAt": 1549065600,
    "position": null,
    "title": null,
    "seoTitle": null
  }
]
JSON;

$expectedFooApiResponse = new Response(
    200,
    ['Content-Type' => 'application/json'],
    $expectedFooAccountApiResponseBody
);

$expectedBarApiResponse = new Response(
    500,
    ['Content-Type' => 'application/json'],
    ''
);

$now = new \DateTime('now', new \DateTimeZone('UTC'));
return [
    [new LoadStylaPages()],
    new StylaPagesMatchConstraint(
        [
            new StylaPageMatchConstraint(
                'Qux page',
                'bar_account',
                'http://example.com',
                '/page/qux-page',
                'Qux page',
                'Qux page seo title',
                null,
                new \DateTime('2019-02-02 00:00:00', new \DateTimeZone('UTC'))
            ),
            new StylaPageMatchConstraint(
                'page/test-st',
                'foo_account',
                'https://example.account.com',
                '/page/test-st',
                'test st',
                'test st',
                null,
                new \DateTime('@1624475094')
            )
        ]
    ),
    new StylaSynchronizationMatchConstraint(
        false,
        StylaPagesSynchronization::STATUS_FAILED,
        $now,
        $now,
        $now,
        $now
    ),
    $expectedFooApiResponse,
    $expectedBarApiResponse,
];
