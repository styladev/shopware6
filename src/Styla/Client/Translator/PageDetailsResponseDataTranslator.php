<?php

namespace Styla\CmsIntegration\Styla\Client\Translator;

use Styla\CmsIntegration\Exception\PageDetailsRequestFiledException;
use Styla\CmsIntegration\Exception\TranslatorException;
use Styla\CmsIntegration\Styla\Client\DTO\PageDetails;

class PageDetailsResponseDataTranslator
{
    use JsonDecodeTrait;

    /**
     * @param string $responseContent
     *
     * @return PageDetails
     * @throws TranslatorException
     */
    public function translate(string $responseContent): PageDetails
    {
        $decodedContent = $this->decodeValue($responseContent);

        if (!array_key_exists('html', $decodedContent)) {
            throw new TranslatorException('Node "html" is not found in the page details response');
        }

        if (isset($decodedContent['error']) && $decodedContent['error']) {
            throw new PageDetailsRequestFiledException(
                sprintf(
                    'Page details request failed, page content: status "%s", responseCode "%s" ',
                    $decodedContent['status'],
                    $decodedContent['responseCode'],
                ),
                $decodedContent['responseCode']
            );
        }

        return new PageDetails(
            $decodedContent['html']['head'] ?? '',
            $decodedContent['html']['body'] ?? '',
            isset($decodedContent['expire']) ? (int)$decodedContent['expire'] : null,
        );
    }
}
