<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exception\ValidationException;
use App\Services\ClientService;
use App\Services\JobService;
use App\Services\PayMethodService;
use App\Services\UserService;
use Doctrine\ORM\EntityManager;
use Valitron\Validator;

class PaymentRequestValidator implements RequestValidatorInterface
{
    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly UserService $userService,
        private readonly JobService $jobService,
        private readonly ClientService $clientService,
        private readonly PayMethodService $payMethodService
    ) {
    }

    public function  validate(array $data): array
    {

        $v = new Validator($data);

        $v->rule('required', ['job', 'date', 'paymethod']);
        $v->rule('required', 'paid')->message('Please check this value');
        $v->rule(
            function ($field, $value, $params, $fields) use (&$data) {
                $id = (int) $data['job'];

                if (!$id) {
                    return false;
                }

                $job = $this->jobService->getById($id);

                if ($job) {
                    $data['job'] = $job;
                    return \true;
                }
                return \false;
            },
            'paid'
        )->message('Unknown job or Deleted Job');
        $v->rule(
            function ($field, $value, $params, $fields) use (&$data) {
                $id = (int) $value;

                if (!$id) {
                    return false;
                }

                $payMethod = $this->payMethodService->getById($id);

                if ($payMethod) {
                    $data['paymethod'] = $payMethod;
                    return \true;
                }
                return \false;
            },
            'paymethod'
        )->message('Unknown payment method');

        if (!$v->validate()) {
            throw new ValidationException($v->errors());
        }
        
        return $data;
    }
}