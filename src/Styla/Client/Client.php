<?php

namespace Styla\CmsIntegration\Styla\Client;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Styla\CmsIntegration\Exception\StylaClientException;
use Styla\CmsIntegration\Styla\Client\Configuration\ClientConfiguration;
use Styla\CmsIntegration\Styla\Client\DTO\PageDetails;
use GuzzleHttp\ClientInterface as GuzzleClient;
use Styla\CmsIntegration\Styla\Client\Translator\PageDetailsResponseDataTranslator;
use Styla\CmsIntegration\Styla\Client\Translator\PagesListResponseDataTranslator;

class Client implements ClientInterface
{
    private ClientConfiguration $configuration;
    private GuzzleClient $guzzleClient;
    private PagesListResponseDataTranslator $pagesListResponseDataTranslator;
    private PageDetailsResponseDataTranslator $pageDetailsResponseDataTranslator;
    private LoggerInterface $logger;

    public function __construct(
        ClientConfiguration $configuration,
        GuzzleClient $guzzleClient,
        PagesListResponseDataTranslator $pagesListResponseDataTranslator,
        PageDetailsResponseDataTranslator $pageDetailsResponseDataTranslator,
        LoggerInterface $logger
    ) {
        $this->configuration = $configuration;
        $this->guzzleClient = $guzzleClient;
        $this->pagesListResponseDataTranslator = $pagesListResponseDataTranslator;
        $this->pageDetailsResponseDataTranslator = $pageDetailsResponseDataTranslator;
        $this->logger = $logger;
    }

    public function getPagesList(int $batchSize): \Generator
    {
        $batchNumber = 1;
        do {
            $pagesList = $this->getPagesBatch($batchNumber++, $batchSize);

            foreach ($pagesList as $page) {
                yield $page;
            }
        } while (count($pagesList) === $batchSize);
    }

    private function getPagesBatch(int $batchNumber, int $batchSize): array
    {
        $request = $this->constructPagesListRequest($batchNumber, $batchSize);

        try {
            $response = $this->guzzleClient->send($request);
            $this->assertStatusCode($response);
            $this->assertHeaders($response);

            return $this->pagesListResponseDataTranslator
                ->translate($response->getBody()->getContents(), $this->configuration);
        } catch (\Throwable $exception) {
            $message = sprintf(
                'Failed to get styla pages list for account %s',
                $this->configuration->getAccountName()
            );

            $this->logger->error($message, ['exception' => $exception]);

            if ($exception instanceof StylaClientException) {
                throw $exception;
            }

            throw new StylaClientException($message, 0, $exception);
        }
    }

    private function constructPagesListRequest(int $batchNumber, int $batchSize): RequestInterface
    {
        $uri = sprintf(
            '%s/v1/delta/%s?limit=%d&offset=%d',
            $this->configuration->getPagesListEndpoint(),
            $this->configuration->getAccountName(),
            $batchSize,
            ($batchNumber - 1) * $batchSize
        );
        $request = new Request('GET', $uri);

        return $request;
    }

    public function getPageData(string $pagePath): PageDetails
    {
        try {
            $request = new Request(
                'GET',
                sprintf(
                    '%s/clients/%s?url=%s',
                    $this->configuration->getPageDetailsEndpoint(),
                    $this->configuration->getAccountName(),
                    urlencode($pagePath)
                )
            );
            $response = $this->guzzleClient->send($request);
            $this->assertStatusCode($response);
            $this->assertHeaders($response);

            return $this->pageDetailsResponseDataTranslator->translate($response->getBody()->getContents());
        } catch (\Throwable $exception) {
            $message = sprintf(
                'Failed to get styla page[path: %s] details for account %s',
                $pagePath,
                $this->configuration->getAccountName()
            );

            $this->logger->error($message, ['exception' => $exception]);

            if ($exception instanceof StylaClientException) {
                throw $exception;
            }

            throw new StylaClientException($message, 0, $exception);
        }
    }

    /**
     * @param ResponseInterface $response
     * @throws StylaClientException
     */
    private function assertStatusCode(ResponseInterface $response)
    {
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new StylaClientException(sprintf('Invalid status code %s', $response->getStatusCode()));
        }
    }

    /**
     * @param ResponseInterface $response
     * @throws StylaClientException
     */
    private function assertHeaders(ResponseInterface $response)
    {
        if (!$response->hasHeader('Content-Type')) {
            throw new StylaClientException('Invalid headers, content type header was not found');
        }
        $contentTypeValues = $response->getHeader('Content-Type');

        $jsonContentTypeFound = false;
        foreach ($contentTypeValues as $contentTypeValue) {
            if (strpos($contentTypeValue, 'application/json') !== false) {
                $jsonContentTypeFound = true;
            }
        }
        if (!$jsonContentTypeFound) {
            throw new StylaClientException(
                sprintf(
                    'Invalid headers, expected json content type, %s found',
                    $response->getHeaderLine('Content-Type')
                )
            );
        }
    }

    /**
     * @return ClientConfiguration
     */
    public function getConfiguration(): ClientConfiguration
    {
        return $this->configuration;
    }
}
