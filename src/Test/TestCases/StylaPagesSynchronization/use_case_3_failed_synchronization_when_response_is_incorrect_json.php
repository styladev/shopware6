<?php

use GuzzleHttp\Psr7\Response;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaPagesSynchronization;
use Styla\CmsIntegration\Test\Constraint\StylaPagesMatchConstraint;
use Styla\CmsIntegration\Test\Constraint\StylaSynchronizationMatchConstraint;

$expectedApiResponseBody = <<<JSON
some response text which is not JSON
JSON;

$expectedApiResponse = new Response(
    200,
    ['Content-Type' => 'application/json'],
    $expectedApiResponseBody
);

$now = new \DateTime('now', new \DateTimeZone('UTC'));
return [
    [],
    new StylaPagesMatchConstraint([]),
    new StylaSynchronizationMatchConstraint(
        false,
        StylaPagesSynchronization::STATUS_FAILED,
        $now,
        $now,
        $now,
        $now
    ),
    $expectedApiResponse
];
