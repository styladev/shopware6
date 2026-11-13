<?php

namespace Styla\CmsIntegration\Controller\Storefront;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Styla\CmsIntegration\UseCase\CategoryApiActionsInteractor;
use Styla\CmsIntegration\UseCase\ProductApiActionsInteractor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ["_routeScope" => ["store-api"]])]
class StylaApiController
{
    public const MAX_LIMIT = 100;

    private CategoryApiActionsInteractor $categoryApiActionsInteractor;
    private ProductApiActionsInteractor $productApiActionsInteractor;

    public function __construct(
        CategoryApiActionsInteractor $categoryApiActionsInteractor,
        ProductApiActionsInteractor $productApiActionsInteractor
    ) {
        $this->categoryApiActionsInteractor = $categoryApiActionsInteractor;
        $this->productApiActionsInteractor = $productApiActionsInteractor;
    }

    #[Route(
        path: "/store-api/styla/categories",
        name: "styla.api.categories",
        methods: ["GET"]
    )]
    public function categoriesListAction(Request $request, SalesChannelContext $context)
    {
        try {
            $categories = $this->categoryApiActionsInteractor->getCategoriesList(
                self::getLimit($request, null),
                self::getPositiveInt($request, 'offset'),
                $context
            );

            return new JsonResponse($categories);
        } catch (\Throwable $exception) {
            throw new HttpException(500, 'Internal server error', $exception);
        }
    }

    #[Route(
        path: "/store-api/styla/products",
        name: "styla.api.products",
        methods: ["GET"]
    )]
    public function productSearchAction(Request $request, SalesChannelContext $context)
    {
        try {
            $products = $this->productApiActionsInteractor->findProducts(
                $request->get('category'),
                $request->get('search', ''),
                $context,
                self::getLimit($request, ProductApiActionsInteractor::PRODUCT_LIST_DEFAULT_LIMIT),
                self::getPositiveInt($request, 'offset'),
            );

            return new JsonResponse($products);
        } catch (\Throwable $exception) {
            throw new HttpException(500, 'Internal server error', $exception);
        }
    }

    #[Route(
        path: "/store-api/styla/product",
        name: "styla.api.product.details",
        methods: ["GET"]
    )]
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

    /**
     * Limits are capped to MAX_LIMIT so the public Store API cannot be asked for unbounded result sets.
     */
    private static function getLimit(Request $request, ?int $default): ?int
    {
        $limit = self::getPositiveInt($request, 'limit') ?? $default;

        return $limit === null ? null : min($limit, self::MAX_LIMIT);
    }

    private static function getPositiveInt(Request $request, string $name): ?int
    {
        $value = filter_var($request->get($name), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $value === false ? null : $value;
    }
}
