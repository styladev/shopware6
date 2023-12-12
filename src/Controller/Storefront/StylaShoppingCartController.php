<?php

namespace Styla\CmsIntegration\Controller\Storefront;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Styla\CmsIntegration\UseCase\ShoppingCartInteractor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 *  @Route(defaults={"_routeScope"={"storefront"}})
 */
class StylaShoppingCartController
{
    private ShoppingCartInteractor $shoppingCartInteractor;

    public function __construct(ShoppingCartInteractor $shoppingCartInteractor)
    {
        $this->shoppingCartInteractor = $shoppingCartInteractor;
    }

    /**
     * @Route(
     *     "/styla/cart/add",
     *     name="styla.api.cart.add",
     *     defaults={"csrf_protected"=false, "XmlHttpRequest"=true},
     *     methods={"POST"}
     * )
     */
    public function addToCartAction(Request $request, Cart $cart, SalesChannelContext $context)
    {
        $productId = $request->request->get('id');
        if (!$productId) {
            throw new BadRequestHttpException('Parameter "id" is required');
        }

        $quantity = $request->request->get('quantity');

        try {
            $this->shoppingCartInteractor->addItemToCurrentUserCart($productId, $quantity, $cart, $context);
        } catch (\Throwable $exception) {
            throw new HttpException(500, 'Internal server error', $exception);
        }

        return new JsonResponse();
    }
}
