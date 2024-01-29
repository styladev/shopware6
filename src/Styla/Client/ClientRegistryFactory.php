<?php

namespace Styla\CmsIntegration\Styla\Client;

use Psr\Log\LoggerInterface;
use Styla\CmsIntegration\Configuration\ConfigurationInterface;
use Styla\CmsIntegration\Styla\Client\Configuration\ClientConfiguration;
use GuzzleHttp\ClientInterface as GuzzleClient;
use Styla\CmsIntegration\Styla\Client\Translator\PageDetailsResponseDataTranslator;
use Styla\CmsIntegration\Styla\Client\Translator\PagesListResponseDataTranslator;

class ClientRegistryFactory
{
    private ConfigurationInterface $configuration;
    private GuzzleClient $guzzleClient;
    private PagesListResponseDataTranslator $listResponseDataTranslator;
    private PageDetailsResponseDataTranslator $pageDetailsResponseDataTranslator;
    private LoggerInterface $logger;
    private string $stylaPagesListEndpoint;
    private string $stylaPageDetailsEndpoint;

    public function __construct(
        ConfigurationInterface $configuration,
        GuzzleClient $guzzleClient,
        PagesListResponseDataTranslator $listResponseDataTranslator,
        PageDetailsResponseDataTranslator $pageDetailsResponseDataTranslator,
        LoggerInterface $logger,
        string $stylaPagesListEndpoint,
        string $stylaPageDetailsEndpoint
    ) {
        $this->configuration = $configuration;
        $this->guzzleClient = $guzzleClient;
        $this->listResponseDataTranslator = $listResponseDataTranslator;
        $this->pageDetailsResponseDataTranslator = $pageDetailsResponseDataTranslator;
        $this->logger = $logger;
        $this->stylaPagesListEndpoint = $stylaPagesListEndpoint;
        $this->stylaPageDetailsEndpoint = $stylaPageDetailsEndpoint;
    }

    public function create()
    {
        $registry = new ClientRegistry();

        $domainUrls = $this->configuration->getDefinedDomainUrls();

        foreach ($this->configuration->getDefinedAccountNames() as $index => $accountName) {
            if ($accountName && $accountName !== '') {
                $domainUrl = isset($domainUrls[$index]) ? $domainUrls[$index] : '';
                $configuration = new ClientConfiguration(
                    $this->stylaPagesListEndpoint,
                    $this->stylaPageDetailsEndpoint,
                    $accountName,
                    $domainUrl
                );

                $client = new Client(
                    $configuration,
                    $this->guzzleClient,
                    $this->listResponseDataTranslator,
                    $this->pageDetailsResponseDataTranslator,
                    $this->logger
                );
                $registry->registerClient($client);
            }
        }

        return $registry;
    }
}
