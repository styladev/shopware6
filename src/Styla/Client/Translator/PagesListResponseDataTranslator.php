<?php

namespace Styla\CmsIntegration\Styla\Client\Translator;

use Psr\Log\LoggerInterface;
use Styla\CmsIntegration\Exception\TranslatorException;
use Styla\CmsIntegration\Styla\Client\Configuration\ClientConfiguration;
use Styla\CmsIntegration\Styla\Client\DTO\GeneralPageInfo;

class PagesListResponseDataTranslator
{
    use JsonDecodeTrait;

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return array|GeneralPageInfo[]
     *
     * @throws TranslatorException
     */
    public function translate(string $responseContent, ClientConfiguration $configuration): array
    {
        $decodedContent = $this->decodeValue($responseContent);
        if (!is_array($decodedContent) || array_values($decodedContent) !== $decodedContent) {
            throw new TranslatorException(
                'API response expected to be a JSON encoded simple array, where each element is key value object'
            );
        }

        $pagesInfo = [];
        foreach ($decodedContent as $pageData) {
            $updatedAt = !empty($pageData['updatedAt'])
                ? $this->tryConvertTimestamp($pageData['updatedAt'])
                : null;
            $deletedAt = !empty($pageData['deletedAt'])
                ? $this->tryConvertTimestamp($pageData['deletedAt'])
                : null;

            $pageInfo = new GeneralPageInfo(
                $configuration->getAccountName(),
                $pageData['name'] ?? '',
                $pageData['domain'] ?? '',
                $pageData['path'] ?? '',
                $pageData['type'] ?? '',
                $updatedAt,
                $deletedAt,
                isset($pageData['position']) ? (int)$pageData['position'] : null,
                $pageData['title'] ?? '',
                $pageData['seoTitle'] ?? '',
            );

            $pagesInfo[] = $pageInfo;
        }

        return $pagesInfo;
    }

    private function tryConvertTimestamp($timestamp): ?\DateTimeInterface
    {
        try {
            return new \DateTimeImmutable('@' . $timestamp, new \DateTimeZone('UTC'));
        } catch (\Throwable $throwable) {
            $this->logger
                ->error(
                    sprintf('Failed to convert timestamp "%s" into date time ', $timestamp),
                    ['exception' => $throwable]
                );

            return null;
        }
    }
}
