<?php

namespace Styla\CmsIntegration\Routing;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StylaUrlGeneratorFactory
{
    private RequestStack $requestStack;
    private EntityRepository $stylaPageRepository;
    private LoggerInterface $logger;

    public function __construct(
        RequestStack $requestStack,
        EntityRepository $stylaPageRepository,
        LoggerInterface $logger
    ) {
        $this->requestStack = $requestStack;
        $this->stylaPageRepository = $stylaPageRepository;
        $this->logger = $logger;
    }

    public function create(UrlGeneratorInterface $decoratedUrlGenerator)
    {
        return new StylaUrlGenerator(
            $decoratedUrlGenerator,
            $this->requestStack,
            $this->stylaPageRepository,
            $this->logger
        );
    }
}
