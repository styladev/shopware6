<?php

use GuzzleHttp\Psr7\Response;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaPagesSynchronization;
use Styla\CmsIntegration\Test\Constraint\StylaPagesMatchConstraint;
use Styla\CmsIntegration\Test\Constraint\StylaSynchronizationMatchConstraint;

$expectedApiResponse = new Response(
    200,
    [],
    ''
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
