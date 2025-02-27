<?php

declare(strict_types = 1);

namespace App\RequestValidators;

use App\Auth;
use App\Contracts\RequestValidatorInterface;
use App\Enum\UserRole;
use App\Exception\ValidationException;
use App\Services\UserService;
use Doctrine\ORM\EntityManager;
use Valitron\Validator;

class UserRoleRequestValidator implements RequestValidatorInterface
{
    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly UserService $userService,
        private readonly Auth $auth
        )
    {
    }

    public function  validate(array $data): array
    {

        $v = new Validator($data);

        $v->rule('required', ['user', 'userRole']);
        $v->rule(
            function ($field, $value, $params, $fields) use (&$data) {
                $id = (int) $value;

                if (!$id) {
                    return false;
                }

                $user = $this->userService->getById($id);

                if ($user) {
                    $data['user'] = $user;
                    return \true;
                }
                return \false;
            },
            'user'
        )->message('Unknown user');
        $v->rule(
            function ($field, $value, $params, $fields) use(&$data) {
            
                $authUser = $this->auth->user();

                $authUserRole = $this->userService->getById($authUser->getId());

                if ($authUserRole->getUserRole() !== UserRole::from('Admin')) {
                    return \false;
                } return true;

            }, 'user'
        )->message('You are not authorized to set role');

        if (!$v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}