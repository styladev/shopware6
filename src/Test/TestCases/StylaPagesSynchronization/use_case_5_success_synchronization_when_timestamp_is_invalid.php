<?php

use GuzzleHttp\Psr7\Response;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaPagesSynchronization;
use Styla\CmsIntegration\Test\Constraint\StylaPageMatchConstraint;
use Styla\CmsIntegration\Test\Constraint\StylaPagesMatchConstraint;
use Styla\CmsIntegration\Test\Constraint\StylaSynchronizationMatchConstraint;

$expectedApiResponseBody = <<<JSON
[
  {
    "name": "",
    "updatedAt": "test 12345",
    "deletedAt": null
  },
  {
    "name": "test",
    "updatedAt": "000000",
    "deletedAt": null
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
                '',
                '',
                '',
                '',
                null,
                null
            ),
            new StylaPageMatchConstraint(
                'test',
                'foo_account',
                '',
                '',
                '',
                '',
                null,
                new \DateTime('1970-01-01T00:00:00+00:00')
            ),
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
