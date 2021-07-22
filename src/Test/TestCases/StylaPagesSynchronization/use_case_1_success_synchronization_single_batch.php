<?php

use GuzzleHttp\Psr7\Response;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaPagesSynchronization;
use Styla\CmsIntegration\Test\Constraint\StylaPageMatchConstraint;
use Styla\CmsIntegration\Test\Constraint\StylaPagesMatchConstraint;
use Styla\CmsIntegration\Test\Constraint\StylaSynchronizationMatchConstraint;

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
    "path": "/page/bar-test-page",
    "type": "PAGE",
    "updatedAt": 1624457941,
    "deletedAt": null,
    "position": null,
    "title": "Bar test page",
    "seoTitle": "Seo Bar test page"
  },
  {
    "name": "",
    "domain": "https://example.account.com",
    "path": "",
    "type": "PAGE",
    "updatedAt": 1624034169,
    "deletedAt": null,
    "position": null,
    "title": "test",
    "seoTitle": "test"
  },
  {
    "name": "home",
    "domain": "https://example.account.com",
    "path": "/home",
    "type": "PAGE",
    "updatedAt": 1595613388,
    "deletedAt": 1595613388,
    "position": null,
    "title": null,
    "seoTitle": null
  },
  {
    "name": "pages/test",
    "domain": "https://example.account.com",
    "path": "/pages/test",
    "type": "PAGE",
    "updatedAt": 1595613774,
    "deletedAt": null,
    "position": null,
    "title": null,
    "seoTitle": null
  },
  {
    "name": "magazine/home",
    "domain": "https://example.account.com",
    "path": "/magazine/home",
    "type": "PAGE",
    "updatedAt": 1595613363,
    "deletedAt": 1595613363,
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
    [],
    new StylaPagesMatchConstraint(
        [
            new StylaPageMatchConstraint(
                '',
                'foo_account',
                'https://example.account.com',
                '',
                'test',
                'test',
                null,
                new \DateTime('@1624034169')
            ),
            new StylaPageMatchConstraint(
                'page/bar-test-page',
                'foo_account',
                'https://example.account.com',
                '/page/bar-test-page',
                'Bar test page',
                'Seo Bar test page',
                null,
                new \DateTime('@1624457941')
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
            ),
            new StylaPageMatchConstraint(
                'pages/test',
                'foo_account',
                'https://example.account.com',
                '/pages/test',
                '',
                '',
                null,
                new \DateTime('@1595613774')
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
