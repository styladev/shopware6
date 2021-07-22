<?php

namespace Styla\CmsIntegration\Styla\Client\Translator;

use Styla\CmsIntegration\Exception\TranslatorException;

trait JsonDecodeTrait
{
    /**
     * @param string $value
     *
     * @return array
     * @throws TranslatorException
     */
    private function decodeValue(string $value): array
    {
        try {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $throwable) {
            throw new TranslatorException(
                sprintf(
                    'Value could not be converted from JSON, reason "%s"',
                    $throwable->getMessage()
                ),
                0,
                $throwable
            );
        }
    }
}
