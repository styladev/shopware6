<?php

namespace Styla\CmsIntegration\Controller\Storefront;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Styla\CmsIntegration\UseCase\ShoppingCartInteractor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ["_routeScope" => ["storefront"]])]
class StylaShoppingCartController
{
    private const DEFAULT_QUANTITY = 1;

    private ShoppingCartInteractor $shoppingCartInteractor;

    public function __construct(ShoppingCartInteractor $shoppingCartInteractor)
    {
        $this->shoppingCartInteractor = $shoppingCartInteractor;
    }

    #[Route(
        path: "/styla/cart/add",
        name: "styla.api.cart.add",
        defaults: [ "csrf_protected" => false, "XmlHttpRequest" => true],
        methods: ["POST"]
    )]
    public function addToCartAction(Request $request, Cart $cart, SalesChannelContext $context)
    {
        $productId = (string) $request->request->get('id', '');
        if (!Uuid::isValid($productId)) {
            throw new BadRequestHttpException('Parameter "id" must be a valid product id');
        }

        $quantity = $request->request->get('quantity', self::DEFAULT_QUANTITY);
        if (filter_var($quantity, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            throw new BadRequestHttpException('Parameter "quantity" must be a positive integer');
        }

        try {
            $this->shoppingCartInteractor->addItemToCurrentUserCart($productId, (int) $quantity, $cart, $context);
        } catch (\Throwable $exception) {
            throw new HttpException(500, 'Internal server error', $exception);
        }

        return new JsonResponse();
    }
}
