<?php

namespace Styla\CmsIntegration\Controller\Storefront;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Styla\CmsIntegration\UseCase\CategoryApiActionsInteractor;
use Styla\CmsIntegration\UseCase\ProductApiActionsInteractor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * use RouteScope(scopes={"store-api"})
 */
class StylaApiController
{
    private CategoryApiActionsInteractor $categoryApiActionsInteractor;
    private ProductApiActionsInteractor $productApiActionsInteractor;

    public function __construct(
        CategoryApiActionsInteractor $categoryApiActionsInteractor,
        ProductApiActionsInteractor $productApiActionsInteractor
    ) {
        $this->categoryApiActionsInteractor = $categoryApiActionsInteractor;
        $this->productApiActionsInteractor = $productApiActionsInteractor;
    }

    /**
     * @Route(
     *     "store-api/styla/categories",
     *     name="styla.api.categories",
     *     methods={"GET"}
     * )
     */
    public function categoriesListAction(Request $request, SalesChannelContext $context)
    {
        try {
            $categories = $this->categoryApiActionsInteractor->getCategoriesList(
                $request->get('limit'),
                $request->get('offset'),
                $context
            );

            return new JsonResponse($categories);
        } catch (\Throwable $exception) {
            throw new HttpException(500, 'Internal server error');
        }
    }

    /**
     * @Route(
     *     "/store-api/styla/products",
     *     name="styla.api.products",
     *     methods={"GET"}
     * )
     */
    public function productSearchAction(Request $request, SalesChannelContext $context)
    {
        try {
            $products = $this->productApiActionsInteractor->findProducts(
                $request->get('category'),
                $request->get('search', ''),
                $context,
                $request->get('limit', ProductApiActionsInteractor::PRODUCT_LIST_DEFAULT_LIMIT),
                $request->get('offset'),
            );

            return new JsonResponse($products);
        } catch (\Throwable $exception) {
            throw new HttpException(500, 'Internal server error', $exception);
        }
    }

    /**
     * @Route(
     *     "/store-api/styla/product",
     *     name="styla.api.product.details",
     *     methods={"GET"}
     * )
     */
    public function productDetailsAction(Request $request, SalesChannelContext $context)
    {
        $productId = $request->get('id');

        if (!$productId) {
            throw new BadRequestHttpException('Product id is required');
        }

        try {
            $productDetails = $this->productApiActionsInteractor->getProductDetails($productId, $context);
        } catch (\Throwable $exception) {
            return new JsonResponse(['error' => 'Product not found', 'saleable' => false]);
        }

        return new JsonResponse($productDetails);
    }
}
