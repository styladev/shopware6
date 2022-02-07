<?php declare(strict_types=1);

namespace Styla\CmsIntegration\Service;

use Psr\Cache\InvalidArgumentException;
use Shopware\Core\Framework\Context;
use Styla\CmsIntegration\Configuration\ConfigurationInterface;
use Styla\CmsIntegration\Exception\StylaClientException;
use Styla\CmsIntegration\Styla\Client\ClientRegistryFactory;
use Symfony\Component\Cache\Adapter\AdapterInterface;


class GetStylaModularContentService
{

    private const STYLA_URL = "https://config.a8.styla.com/v2/boot?q=";

    private AdapterInterface $cache;

    private ClientRegistryFactory $clientRegistryFactory;

    private ConfigurationInterface $configuration;

    private $slotID;

    public function __construct(AdapterInterface $cache, ClientRegistryFactory $clientRegistryFactory, ConfigurationInterface $configuration)
    {
        $this->cache = $cache;
        $this->clientRegistryFactory = $clientRegistryFactory;
        $this->configuration = $configuration;
    }

    /**
     * @param $id
     * @return false|mixed
     * @throws InvalidArgumentException
     */
    public function checkSlotCash($id)
    {
        $this->slotID = $id;
        $cacheItemContent = $this->cache->getItem($id)->get();

        if (!empty($cacheItemContent)) {
            return $cacheItemContent;
        }

        return $this->getModularContentPath($id);
    }

    /**
     * @param $id
     * @return false
     */
    private function getModularContentPath($id)
    {

        $token = bin2hex(openssl_random_pseudo_bytes(64));

        $queryParams = '{"path":"/","slots":["' . $id . '"]}';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => self::STYLA_URL . $queryParams,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'token:' . $token
            ),
        ));

        $response = curl_exec($curl);
        $settings = json_decode($response, true);
        $path = $settings['slots'][0][1]['path'];

        if (!empty($path)) {
            return $this->getRenderedContent($path);
        }

        return false;
    }

    /**
     * @param $path
     * @return false
     */
    private function getRenderedContent($path)
    {

        $languageId = Context::createDefaultContext()->getLanguageId();
        $accountName = $this->configuration->getAccountNameByLanguage($languageId);

        $clientFactory = $this->clientRegistryFactory->create();

        $client = $clientFactory->getClientByAccountName($accountName);

        try {
            $content = $client->getPageData($path)->getBody();
        } catch (StylaClientException $e) {

        }

        if (!empty($content)) {
           return $this->writeSlotCache($content);
        }

        return false;
    }

    /**
     * @param $content
     * @return mixed
     */
    private function writeSlotCache($content)
    {
        try {
            $cacheItem = $this->cache->getItem($this->slotID);
            $cacheItem->set($content);
            $cacheItem->expiresAfter($this->configuration->getPageDetailsCacheDuration());
            $this->cache->save($cacheItem);
        } catch (InvalidArgumentException $e) {
        }

        return $content;
    }
}