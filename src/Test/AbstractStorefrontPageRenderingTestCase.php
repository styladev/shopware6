<?php

namespace Styla\CmsIntegration\Test;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\HttpKernel;
use Shopware\Core\PlatformRequest;
use Shopware\Core\SalesChannelRequest;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class AbstractStorefrontPageRenderingTestCase extends AbstractTestCase
{
    private static ?HttpKernel $kernel = null;

    protected function getHttpKernel(): HttpKernel
    {
        if (!self::$kernel) {
            $kernel = new HttpKernel('test', false, KernelLifecycleManager::getClassLoader());

            $kernel->getKernel()->boot();

            self::$kernel = $kernel;
        }

        return self::$kernel;
    }

    /**
     * @after
     */
    public function stopTransactionAfter(): void
    {
        parent::stopTransactionAfter();

        if (self::$kernel) {
            self::$kernel->getKernel()->shutdown();
            self::$kernel = null;
        }
    }



    /**
     * This results in the test container, with all private services public
     */
    protected function getContainer(): ContainerInterface
    {
        $container = $this->getHttpKernel()->getKernel()->getContainer();

        if (!$container->has('test.service_container')) {
            throw new \RuntimeException('Unable to run tests against kernel without test.service_container');
        }

        return $container->get('test.service_container');
    }

    protected function createValidTestRequest(string $uri, ?LanguageEntity $languageEntity = null): Request
    {
        $request = Request::create($uri);

        if ($languageEntity) {
            $request->headers->set(PlatformRequest::HEADER_LANGUAGE_ID, [$languageEntity->getId()]);
        }

        $contextToken = Uuid::randomHex();
        $salesChannelContext = $this->getContainer()->get(SalesChannelContextFactory::class)->create(
            $contextToken,
            Defaults::SALES_CHANNEL
        );
        $request->attributes->set(SalesChannelRequest::ATTRIBUTE_IS_SALES_CHANNEL_REQUEST, true);
        $request->attributes->set(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT, $salesChannelContext);
        $request->attributes->set(RequestTransformer::STOREFRONT_URL, 'shopware.test');
        $request->setSession($this->getContainer()->get('session'));

        return $request;
    }
}
