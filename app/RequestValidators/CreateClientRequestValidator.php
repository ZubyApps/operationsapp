<?php

declare(strict_types = 1);

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Entity\Client;
use App\Exception\ValidationException;
use Doctrine\ORM\EntityManager;
use Valitron\Validator;

class CreateClientRequestValidator implements RequestValidatorInterface
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
        $v->rule(
            fn ($field, $value, $params, $fields) => !$this->entityManager->getRepository(Client::class)->count(
                ['phoneNumber' => $value]
            ),
            'number'
        )->message('Client with this Phone no. already exists');
        

        if (! $v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}