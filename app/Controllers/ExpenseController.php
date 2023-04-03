<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\DataObjects\ExpenseData;
use App\Entity\Expense;
use App\RequestValidators\ExpenseRequestValidator;
use App\ResponseFormatter;
use App\Services\CategoryService;
use App\Services\ExpenseService;
use App\Services\RequestService;
use App\Services\SponsorService;
use App\Services\UserService;
use DateTime;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class ExpenseController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestService $requestService,
        private readonly UserService $userService,
        private readonly ExpenseService $expenseService,
        private readonly CategoryService $categoryService,
        private readonly SponsorService $sponsorService
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response, 
            'expense/index.twig',
            [
                'categories'    => $this->categoryService->getCategories(),
                'sponsors'      => $this->sponsorService->getSponsors()
            ]

        );
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(ExpenseRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->expenseService->create(
            new ExpenseData(
                $data['category'],
                $data['sponsor'],
                new DateTime($data['date']),
                $data['description'],
                (float)$data['amount']),
            $request->getAttribute('user'));

        return $response->withHeader('Location', '/expenses')->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->expenseService->delete((int) $args['id']);

        return $response;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $expense = $this->expenseService->getById((int) $args['id']);

        if (! $expense) {
            return $response->withStatus(404);
        }

        $data = [
            'id'            => $expense->getId(),
            'date'          => $expense->getDate()->format('Y-m-d'),
            'category'      => $expense->getCategory()->getId(),
            'sponsor'       => $expense->getSponsor()->getId(),
            'description'   => $expense->getDescription(),
            'amount'        => $expense->getAmount(),
        ];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(ExpenseRequestValidator::class)->validate(
            $args + $request->getParsedBody()
        );

        $expense = $this->expenseService->getById((int) $data['id']);

        if (! $expense) {
            return $response->withStatus(404);
        }

        $this->expenseService->update($expense,
            new ExpenseData(
                $data['category'],
                $data['sponsor'],
                new DateTime($data['date']),
                $data['description'],
                (float)$data['amount']
            ),
            $request->getAttribute('user')
        );

        return $response;
    }

    public function load(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $expenses       = $this->expenseService->getPaginatedExpenses($params);
        $transformer    = function (Expense $expense) {
            return [
                'id'            => $expense->getId(),
                'date'          => $expense->getDate()->format('d-M-Y'),
                'sponsor'       => $expense->getSponsor()->getName(),
                'category'      => $expense->getCategory()->getName(),
                'description'   => $expense->getDescription(),
                'amount'        => $expense->getAmount(),
                'flag'          => $expense->getSponsor()->getFlag(),
                'activeUser'    => $this->userService->getActiveUserRole(),
                'createdAt'     => $expense->getCreatedAt()->format('d-m-Y g:ia'),
            ];
        };

        $totalExpenses = count($expenses);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $expenses->getIterator()),
            $params->draw,
            $totalExpenses
        );
    }

    public function expenseList(Request $request, Response $response): Response
    {
        $data = $this->expenseService->getAll();

        return $this->responseFormatter->asJson($response,  $data);
    }

    public function loadDetails(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $expenses       = $this->expenseService->getPaginatedExpenseDetails($params, $request->getQueryParams());
        $transformer    = function (Expense $expense) {
            return [
                'id'                => $expense->getId(),
                'date'              => $expense->getDate()->format('d-M-y g:ia'),
                'paymethod'         => $expense->getCategory(),
                'paymethod'         => $expense->getDescription(),
                'amount'            => $expense->getAmount(),
                'staff'             => $expense->getUser()->getFirstname(),
                'activeUser'        => $this->userService->getActiveUserRole(),
            ];
        };

        $totalExpenses = count($expenses);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $expenses->getIterator()),
            $params->draw,
            $totalExpenses
        );
    }
}
