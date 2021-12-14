<?php

namespace Styla\CmsIntegration\Test;

use Doctrine\DBAL\Connection;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Styla\CmsIntegration\Configuration\ConfigurationFactory;
use Styla\CmsIntegration\Test\DataFixtures\DataFixturesExecutor;
use Styla\CmsIntegration\Test\DataFixtures\ReferencesRegistry;
use Styla\CmsIntegration\Test\Stub\GuzzleClientTestProxy;

abstract class AbstractTestCase extends TestCase
{
    use IntegrationTestBehaviour;

    protected SystemConfigService $systemConfigService;
    protected Connection $connection;
    protected EntityRepositoryInterface $stylaPagesRepository;
    protected MockObject $guzzleClient;
    protected DataFixturesExecutor $dataFixturesExecutor;
    protected ReferencesRegistry $referenceRegistry;

    protected function setUp(): void
    {
        $this->referenceRegistry = new ReferencesRegistry();
        $this->dataFixturesExecutor = new DataFixturesExecutor($this->referenceRegistry);

        $container = $this->getContainer();

        /** @var Connection $connection */
        $connection = $container->get(Connection::class);
        $this->connection = $connection;

        /** @var SystemConfigService $systemConfigService */
        $systemConfigService = $this->getContainer()->get(SystemConfigService::class);
        $this->systemConfigService = $systemConfigService;

        /** @var EntityRepositoryInterface $repository */
        $repository = $this->getContainer()->get('styla_cms_page.repository');
        $this->stylaPagesRepository = $repository;

        $this->setStylaSystemSettings();

        $this->guzzleClient = $this->createMock(ClientInterface::class);

        /** @var GuzzleClientTestProxy $client */
        $client = $this->getContainer()->get('styla.cms_integration.guzzle_client');
        $client->setProxiedClient($this->guzzleClient);
    }

    protected function executeFixtures(array $fixtures)
    {
        $this->dataFixturesExecutor->executeDataFixtures($this->getContainer(), $fixtures);
    }

    private function setStylaSystemSettings()
    {
        $this->systemConfigService
            ->set(ConfigurationFactory::PREFIX . 'accountNames', ['default' => 'foo_account']);
    }

    protected function getByReference(string $reference): Entity
    {
        return $this->referenceRegistry->getByReference($reference);
    }

    protected function setByReference(string $reference, $value)
    {
        $this->referenceRegistry->setReference($reference, $value);
    }

    /**
     * Isolation is not working properly in some cases so it is safer to clear container after test
     * to avoid problems with the isolation
     */
    public static function tearDownAfterClass(): void
    {
        KernelLifecycleManager::ensureKernelShutdown();
    }
}
