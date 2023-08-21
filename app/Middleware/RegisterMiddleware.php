<?php

declare(strict_types = 1);

namespace App\Middleware;

use App\Contracts\AuthInterface;
use App\Services\RequestService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RegisterMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly RequestService $requestService,
        private readonly AuthInterface $auth,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $userRole =  $this->auth->user()->getUserRole()->name;
        
        if ($userRole === 'Edit' || $userRole === 'Admin'){
            return $handler->handle($request);
        }

        $referer  = $this->requestService->getReferer($request);

        return $this->responseFactory->createResponse(302)->withHeader('Location', $referer);
    }
}