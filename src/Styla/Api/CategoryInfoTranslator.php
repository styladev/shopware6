<?php

namespace Styla\CmsIntegration\Styla\Api;

use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Styla\CmsIntegration\Styla\Api\DTO\Category\CategoryInfo;
use Styla\CmsIntegration\Styla\Api\DTO\Category\CategoryInfoList;

class CategoryInfoTranslator
{
    public const DEFAULT_MAX_TRANSLATION_NESTING_LEVEL = 500;

    private EntityRepository $categoryRepository;

    public function __construct(EntityRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function translateCategoriesList(
        CategoryCollection $categoryCollection,
        Context $context,
        int $maxNestingLevel = self::DEFAULT_MAX_TRANSLATION_NESTING_LEVEL
    ): CategoryInfoList {
        $categoryInfoList = new CategoryInfoList();
        foreach ($categoryCollection as $categoryEntity) {
            $categoryInfo = $this->translateCategory($categoryEntity, $context, $maxNestingLevel);
            $categoryInfoList->add($categoryInfo);
        }

        return $categoryInfoList;
    }

    public function translateCategory(
        CategoryEntity $categoryEntity,
        Context $context,
        int $maxNestingLevel = self::DEFAULT_MAX_TRANSLATION_NESTING_LEVEL
    ): CategoryInfo {
        $maxNestingLevel--;

        $children = $this->fetchCategoriesChildren($categoryEntity, $context);

        $childrenCategoriesInfoList = $maxNestingLevel > 0
            ? $this->translateCategoriesList($children, $context, $maxNestingLevel)
            : new CategoryInfoList();

        return new CategoryInfo(
            $categoryEntity->getId(),
            $categoryEntity->getName(),
            $childrenCategoriesInfoList
        );
    }

    private function fetchCategoriesChildren(CategoryEntity $categoryEntity, Context $context): CategoryCollection
    {
        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('parentId', $categoryEntity->getId()));

        $result = $this->categoryRepository->search($criteria, $context);

        return $result->getEntities();
    }
}
