<?php

declare(strict_types = 1);

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Entity\Client;
use App\Exception\ValidationException;
use Doctrine\ORM\EntityManager;
use Valitron\Validator;

class UpdateClientRequestValidator implements RequestValidatorInterface
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function validate(array $data): array
    {
        $v = new Validator($data);

        $v->rule('required', ['name', 'number']);
        $v->rule('lengthMax', 'name', 50);
        $v->rule('lengthMin', 'number', 11);
        $v->rule('lengthMax', 'number', 11);
        $v->rule('email', 'email');
        $v->rule('numeric', 'number');

        if (! $v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}