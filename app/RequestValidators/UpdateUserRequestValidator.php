<?php

declare(strict_types = 1);

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exception\ValidationException;
use App\Services\DepartmentService;
use Doctrine\ORM\EntityManager;
use Valitron\Validator;

class UpdateUserRequestValidator implements RequestValidatorInterface
{
    public function __construct(
        private readonly EntityManager $entityManager, 
        private readonly DepartmentService $departmentService
        )
    {
    }

    public function  validate(array $data): array
    {

        $v = new Validator($data);

        $v->rule('required', ['firstname', 'lastname', 'email', 'phonenumber']);
        $v->rule('email', 'email');
        $v->rule('equals', 'conpassword', 'password')->label('Confirm Password');
        $v->rule('numeric', 'phonenumber');
        $v->rule(
            function ($field, $value, $params, $fields) use (&$data) {
                
                if ($value === 'U') {
                    $data['department'] = null;
                    return \true;
                }
                $id = (int) $value;

                if (!$id) {
                    return false;
                }

                $department = $this->departmentService->getById($id);

                if ($department) {
                    $data['department'] = $department;
                    return \true;
                }
                return \false;
            },
            'department'
        )->message('Unkown Department');

        if (!$v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}