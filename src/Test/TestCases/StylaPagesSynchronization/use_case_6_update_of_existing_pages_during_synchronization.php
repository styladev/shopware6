<?php

use GuzzleHttp\Psr7\Response;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaPagesSynchronization;
use Styla\CmsIntegration\Test\Constraint\StylaPageMatchConstraint;
use Styla\CmsIntegration\Test\Constraint\StylaPagesMatchConstraint;
use Styla\CmsIntegration\Test\Constraint\StylaSynchronizationMatchConstraint;
use Styla\CmsIntegration\Test\DataFixtures\LoadStylaPages;

$expectedApiResponseBody = <<<JSON
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
    "name": "Updated home page name",
    "domain": "https://example.account.com",
    "path": "",
    "type": "PAGE",
    "updatedAt": 1624034169,
    "deletedAt": null,
    "position": null,
    "title": "Updated home page title",
    "seoTitle": "Updated home page seo title"
  },
  {
    "name": "Qux page",
    "domain": "https://example.account.com",
    "path": "/page/qux-page",
    "type": "PAGE",
    "updatedAt": 1549065600,
    "deletedAt": null,
    "position": null,
    "title": null,
    "seoTitle": null
  }
]
JSON;

$expectedApiResponse = new Response(
    200,
    ['Content-Type' => 'application/json'],
    $expectedApiResponseBody
);

$now = new \DateTime('now', new \DateTimeZone('UTC'));
return [
    [new LoadStylaPages()],
    new StylaPagesMatchConstraint(
        [
            new StylaPageMatchConstraint(
                'Updated home page name',
                'foo_account',
                'https://example.account.com',
                '',
                'Updated home page title',
                'Updated home page seo title',
                null,
                new \DateTime('@1624034169')
            ),
            new StylaPageMatchConstraint(
                'Qux page',
                'foo_account',
                'https://example.account.com',
                '/page/qux-page',
                '',
                '',
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
        StylaPagesSynchronization::STATUS_SUCCESS,
        $now,
        $now,
        $now,
        $now
    ),
    $expectedApiResponse
];
