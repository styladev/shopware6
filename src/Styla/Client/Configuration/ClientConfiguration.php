<?php

namespace Styla\CmsIntegration\Styla\Client\Configuration;

class ClientConfiguration
{
    private string $pagesListEndpoint;
    private string $pageDetailsEndpoint;
    private string $accountName;
    private string $domainUrl;

    public function __construct(string $pagesListEndpoint, string $pageDetailsEndpoint, string $accountName, string $domainUrl)
    {
        $this->pagesListEndpoint = $pagesListEndpoint;
        $this->pageDetailsEndpoint = $pageDetailsEndpoint;
        $this->accountName = $accountName;
        $this->domainUrl = $domainUrl;
    }

    /**
     * @return string
     */
    public function getPagesListEndpoint(): string
    {
        return $this->pagesListEndpoint;
    }

    /**
     * @return string
     */
    public function getPageDetailsEndpoint(): string
    {
        return $this->pageDetailsEndpoint;
    }

    /**
     * @return string
     */
    public function getAccountName(): string
    {
        return $this->accountName;
    }

    /**
     * @return string
     */
    public function getDomainUrl(): string
    {
        return $this->domainUrl;
    }
}
