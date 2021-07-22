<?php

namespace Styla\CmsIntegration\Styla\Api\DTO\Product;

use Shopware\Core\Framework\Struct\Collection;

class ProductInfoList extends Collection
{
    public function jsonSerialize(): array
    {
        return $this->getElements();
    }

    protected function getExpectedClass(): ?string
    {
        return ProductInfo::class;
    }
}
