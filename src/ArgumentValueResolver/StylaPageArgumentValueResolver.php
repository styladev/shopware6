<?php

namespace Styla\CmsIntegration\ArgumentValueResolver;

use Styla\CmsIntegration\Entity\StylaPage\StylaPage;
use Styla\CmsIntegration\EventSubscriber\StorefrontRequestControllerSubstituteEventSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class StylaPageArgumentValueResolver implements ValueResolverInterface
{
    /**
     * Symfony does not call a supports() method for ValueResolverInterface implementations,
     * so resolve() must return an empty list for every argument it is not responsible for.
     * Otherwise it shadows all lower priority resolvers (e.g. Shopware's Criteria resolver).
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== StylaPage::class) {
            return [];
        }

        return [$request->attributes->get(StorefrontRequestControllerSubstituteEventSubscriber::STYLA_PAGE_INSTANCE_ARGUMENT)];
    }
}
