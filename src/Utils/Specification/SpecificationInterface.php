<?php

namespace Styla\CmsIntegration\Utils\Specification;

interface SpecificationInterface
{
    /**
     * @param mixed $value
     *
     * @return bool
     * @throws \Throwable
     */
    public function isSatisfiedBy($value): bool;
}
