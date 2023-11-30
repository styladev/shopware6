<?php

namespace Styla\CmsIntegration\Styla\Api;

use Shopware\Core\Content\Media\MediaType\ImageType;
use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Styla\CmsIntegration\Styla\Api\DTO\Product\ProductInfo;
use Styla\CmsIntegration\Styla\Api\DTO\Product\ProductInfoList;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductInfoTranslator
{
    protected UrlGeneratorInterface $urlGenerator;
    protected EntityRepository $productMediaRepository;

    public function __construct(UrlGeneratorInterface $urlGenerator, EntityRepository $productMediaRepository)
    {
        $this->urlGenerator = $urlGenerator;
        $this->productMediaRepository = $productMediaRepository;
    }

    public function translateToProductInfoList(ProductCollection $productCollection, Context $context): ProductInfoList
    {
        $productInfoList = new ProductInfoList();

        foreach ($productCollection as $productEntity) {
            $productInfo = $this->translateToProductInfo($productEntity, $context);
            $productInfoList->add($productInfo);
        }

        return $productInfoList;
    }

    protected function translateToProductInfo(ProductEntity $productEntity, Context $context): ProductInfo
    {
        $productDetailsUrl = $this->urlGenerator
            ->generate(
                'frontend.detail.page',
                ['productId' => $productEntity->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productId', $productEntity->getId()));
        $criteria->addAssociation('media');

        $mediaEntitiesResult = $this->productMediaRepository->search($criteria, $context);

        $images = [];
        /** @var ProductMediaEntity $productMedia */
        foreach ($mediaEntitiesResult as $productMedia) {
            $media = $productMedia->getMedia();

            if ($media->getMediaType() instanceof ImageType) {
                $images[] = $media->getUrl();
            }
        }

        return new ProductInfo(
            $productEntity->getId(),
            $productEntity->getName() ?? '',
            $images,
            $productDetailsUrl
        );
    }
}
