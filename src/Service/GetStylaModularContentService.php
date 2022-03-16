<?php declare(strict_types=1);

namespace Styla\CmsIntegration\Service;

use GuzzleHttp\Client;
use Shopware\Core\Framework\Context;
use Styla\CmsIntegration\Configuration\ConfigurationInterface;
use Styla\CmsIntegration\Styla\Client\ClientRegistry;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class GetStylaModularContentService
{
    private const STYLA_URL = "https://config.a8.styla.com/v2/boot?q=";
    private TagAwareAdapterInterface $cache;
    private ClientRegistry $clientRegistry;
    private ConfigurationInterface $configuration;

    public function __construct(
        TagAwareAdapterInterface $cache,
        ClientRegistry $clientRegistry,
        ConfigurationInterface $configuration
    ) {
        $this->cache = $cache;
        $this->clientRegistry = $clientRegistry;
        $this->configuration = $configuration;
    }

    public function execute(Context $context, string $slotId): string
    {
        $cacheItem = $this->cache->getItem($slotId);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $slotContent = $this->loadSlotContent($context, $slotId);
        $cacheItem->set($slotContent);
        $cacheItem->expiresAfter($this->configuration->getPageDetailsCacheDuration());
        $this->cache->save($cacheItem);

        return $slotContent;
    }

    private function loadSlotContent(Context $context, string $slotId): string
    {
        $content = '';
        $accountName = $this->configuration->getAccountNameByLanguage($context->getLanguageId());
        $client = $this->clientRegistry->getClientByAccountName($accountName);

        foreach ($this->resolveModularContentNamesBySlotId($slotId) as $modularContentPath) {
            $content .= $client->getPageData($modularContentPath)->getBody();
        }

        return $content;
    }

    private function resolveModularContentNamesBySlotId(string $slotId): array
    {
        $queryParams = '{"path":"/","slots":["' . $slotId . '"]}';

        $client = new Client();

        $response = $client->get(self::STYLA_URL . $queryParams, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $response = json_decode($response->getBody()->getContents(), true);

        $path = $response['slots'][0][1]["path"] ?? null;

        return array_filter([$path]);
    }
}