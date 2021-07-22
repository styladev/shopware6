<?php

namespace Styla\CmsIntegration\Exception;

class UseCaseInteractorException extends \Exception implements StylaExceptionInterface
{
    public const CODE_SYNCHRONIZATION_FAILED_TO_START = 'SYNCHRONIZATION_FAILED_TO_START';
    public const CODE_SYNCHRONIZATION_PROCESS_FAILED = 'SYNCHRONIZATION_PROCESS_FAILED';
    public const CODE_FAILED_TO_GET_PAGE_DETAILS = 'FAILED_TO_GET_PAGE_DETAILS';
    public const CODE_FAILED_TO_GET_PRODUCT_DETAILS = 'FAILED_TO_GET_PRODUCT_DETAILS';
    public const CODE_FAILED_TO_GET_PRODUCT_LIST = 'FAILED_TO_GET_PRODUCTS_LIST';
    public const CODE_FAILED_TO_GET_CATEGORIES_LIST = 'FAILED_TO_GET_CATEGORIES_LIST';
    public const CODE_FAILED_TO_ADD_ITEM_TO_CART = 'FAILED_TO_ADD_ITEM_TO_CART';

    protected ?string $errorCode = '';

    public function __construct(string $message, ?string $code = null, \Throwable $previous = null)
    {
        $this->errorCode = $code;

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return mixed|string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
}
