<?php

namespace Styla\CmsIntegration\UseCase;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Styla\CmsIntegration\Exception\UseCaseInteractorException;

class ShoppingCartInteractor
{
    private LineItemFactoryRegistry $lineItemFactory;
    private CartService $cartService;
    private LoggerInterface $logger;

    public function __construct(
        LineItemFactoryRegistry $lineItemFactory,
        CartService $cartService,
        LoggerInterface $logger
    ) {
        $this->lineItemFactory = $lineItemFactory;
        $this->cartService = $cartService;
        $this->logger = $logger;
    }

    /**
     * Adds the product through Shopware's CartService, so the regular add-to-cart flow runs
     * (cart rules, purchase limits, calculation, persisting and the cart events).
     *
     * @throws UseCaseInteractorException
     */
    public function addItemToCurrentUserCart(
        string $productId,
        int $qty,
        Cart $cart,
        SalesChannelContext $salesChannelContext
    ): Cart {
        try {
            $lineItem = $this->lineItemFactory->create(
                [
                    'id' => $productId,
                    'referencedId' => $productId,
                    'quantity' => $qty,
                    'type' => LineItem::PRODUCT_LINE_ITEM_TYPE,
                ],
                $salesChannelContext
            );

            return $this->cartService->add($cart, $lineItem, $salesChannelContext);
        } catch (\Throwable $exception) {
            $message = sprintf(
                'Could not add line item[id: %s, qty: %s] to the cart, reason: %s',
                $productId,
                $qty,
                $exception->getMessage()
            );
            $this->logger->error($message, ['exception' => $exception]);

            throw new UseCaseInteractorException(
                $message,
                UseCaseInteractorException::CODE_FAILED_TO_ADD_ITEM_TO_CART,
                $exception
            );
        }
    }
}
