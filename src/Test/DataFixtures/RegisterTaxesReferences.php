<?php

namespace Styla\CmsIntegration\Test\DataFixtures;

use Psr\Container\ContainerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Tax\TaxEntity;

class RegisterTaxesReferences implements TestDataFixturesInterface
{
    public function execute(ContainerInterface $container, ReferencesRegistry $referencesRegistry)
    {
        /** @var EntityRepositoryInterface $taxRepository */
        $taxRepository = $container->get('tax.repository');
        $taxes = $taxRepository->search(new Criteria(), Context::createDefaultContext())->getEntities();

        /** @var TaxEntity $tax */
        foreach ($taxes as $tax) {
            switch ($tax->getName()) {
                case 'Standard rate':
                    $referencesRegistry->setReference('styla_cms_integration.tax.standard', $tax);
                    continue 2;
                case 'Reduced rate':
                    $referencesRegistry->setReference('styla_cms_integration.tax.reduced', $tax);
                    continue 2;
                case 'Reduced rate 2':
                    $referencesRegistry->setReference('styla_cms_integration.tax.reduced2', $tax);
                    continue 2;
            }
        }
    }
}
