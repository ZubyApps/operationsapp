<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\Entity\Category;
use App\RequestValidators\CategoryRequestValidator;
use App\ResponseFormatter;
use App\Services\CategoryService;
use App\Services\RequestService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class CategoryController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly CategoryService $categoryService,
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestService $requestService
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'expense/category/index.twig');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(CategoryRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->categoryService->create($data, $request->getAttribute('user'));

        return $response->withHeader('Location', '/category')->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->categoryService->delete((int) $args['id']);

        return $response;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $category = $this->categoryService->getById((int) $args['id']);

        if (! $category) {
            return $response->withStatus(404);
        }

        $data = [
            'id'            => $category->getId(), 
            'name'          => $category->getName(),
            'description'   => $category->getDescription()
        ];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(CategoryRequestValidator::class)->validate(
            $args + $request->getParsedBody()
        );

        $category = $this->categoryService->getById((int) $data['id']);

        if (! $category) {
            return $response->withStatus(404);
        }

        $this->categoryService->update($category, $data, $request->getAttribute('user'));

        return $response;
    }

    public function load(Request $request, Response $response): Response
    {
        $params      = $this->requestService->getDataTableQueryParameters($request);
        $categories  = $this->categoryService->getPaginatedCategories($params);
        $transformer = function (Category $category) {
            return [
                'id'            => $category->getId(),
                'name'          => $category->getName(),
                'description'   => $category->getDescription(),
                'count'         => $category->getExpenses()->count(),
                'total'         => $category->getExpenseTotal(),
                'createdAt'     => $category->getCreatedAt()->format('m/d/Y g:i A'),
            ];
        };

        $totalcategories = count($categories);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $categories->getIterator()),
            $params->draw,
            $totalcategories
        );
    }

    public function categoryList(Request $request, Response $response): Response
    {
        $data = $this->categoryService->getCategories();

        return $this->responseFormatter->asJson($response,  $data);
    }

}
