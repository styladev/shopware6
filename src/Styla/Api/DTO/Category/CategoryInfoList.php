<?php

namespace Styla\CmsIntegration\Styla\Api\DTO\Category;

use Shopware\Core\Framework\Struct\Collection;

class CategoryInfoList extends Collection
{
    public function jsonSerialize(): array
    {
        return $this->getElements();
    }

    protected function getExpectedClass(): ?string
    {
        return CategoryInfo::class;
    }
}
