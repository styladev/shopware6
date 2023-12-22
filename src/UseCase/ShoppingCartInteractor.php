<?php

namespace Styla\CmsIntegration\UseCase;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartCalculator;
use Shopware\Core\Checkout\Cart\CartPersister;
use Shopware\Core\Checkout\Cart\Event\AfterLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\Event\CartChangedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Styla\CmsIntegration\Exception\UseCaseInteractorException;

class ShoppingCartInteractor
{
    private LineItemFactoryRegistry $lineItemFactory;
    private EventDispatcherInterface $eventDispatcher;
    private CartCalculator $cartCalculator;
    private CartPersister $cartPersister;
    private LoggerInterface $logger;

    public function __construct(
        LineItemFactoryRegistry $lineItemFactory,
        EventDispatcherInterface $eventDispatcher,
        CartCalculator $cartCalculator,
        CartPersister $cartPersister,
        LoggerInterface $logger
    ) {
        $this->lineItemFactory = $lineItemFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->cartCalculator = $cartCalculator;
        $this->cartPersister = $cartPersister;
        $this->logger = $logger;
    }

    /**
     * Should be in sync with @see \Shopware\Core\Checkout\Cart\SalesChannel\CartItemAddRoute::add
     *
     * @param string $productId
     * @param int $qty
     * @param Cart $cart
     * @param SalesChannelContext $salesChannelContext
     *
     * @throws UseCaseInteractorException
     */
    public function addItemToCurrentUserCart(
        string $productId,
        int $qty,
        Cart $cart,
        SalesChannelContext $salesChannelContext
    ) {
        try {
            $item = [
                'id' => $productId,
                'referencedId' => $productId,
                'quantity' => $qty,
                'type' => LineItem::PRODUCT_LINE_ITEM_TYPE
            ];
            $lineItem = $this->lineItemFactory->create($item, $salesChannelContext);

            $alreadyExists = $cart->has($lineItem->getId());
            $cart->add($lineItem);

            $this->eventDispatcher
                ->dispatch(new BeforeLineItemAddedEvent($lineItem, $cart, $salesChannelContext, $alreadyExists));

            $cart->markModified();

            $cart = $this->cartCalculator->calculate($cart, $salesChannelContext);
            $this->cartPersister->save($cart, $salesChannelContext);

            $this->eventDispatcher->dispatch(new AfterLineItemAddedEvent([$lineItem], $cart, $salesChannelContext));
            $this->eventDispatcher->dispatch(new CartChangedEvent($cart, $salesChannelContext));
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
