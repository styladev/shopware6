<?php

namespace Styla\CmsIntegration\Styla\Client;

use Styla\CmsIntegration\Exception\StylaClientException;
use Styla\CmsIntegration\Styla\Client\Configuration\ClientConfiguration;
use Styla\CmsIntegration\Styla\Client\DTO\GeneralPageInfo;
use Styla\CmsIntegration\Styla\Client\DTO\PageDetails;

interface ClientInterface
{
    /**
     * @param int $batchSize
     *
     * @return \Generator|GeneralPageInfo[]
     */
    public function getPagesList(int $batchSize): \Generator;

    /**
     * @param string $pagePath
     *
     * @return PageDetails
     * @throws StylaClientException
     */
    public function getPageData(string $pagePath): PageDetails;

    public function getConfiguration(): ClientConfiguration;
}
