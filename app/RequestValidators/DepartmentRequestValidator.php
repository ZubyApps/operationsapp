<?php

declare(strict_types = 1);

namespace App\RequestValidators;

use App\Auth;
use App\Contracts\RequestValidatorInterface;
use App\Enum\UserRole;
use App\Exception\ValidationException;
use App\Services\UserService;
use Valitron\Validator;

class DepartmentRequestValidator implements RequestValidatorInterface
{
    public function __construct(private readonly Auth $auth, private readonly UserService $userService)
    {
        
    }

    public function validate(array $data): array
    {
        $v = new Validator($data);

        $v->rule('required', ['name', 'description', 'head']);
        $v->rule('lengthMax', 'description', 255);
        $v->rule(
            function ($field, $value, $params, $fields) use (&$data) {
                $id = (int) $value;

                if (!$id) {
                    return false;
                }

                $user = $this->userService->getById($id);

                if ($user) {
                    
                    $data['head'] = $user;

                    return \true;
                }
                return \false;
            },
            'head'
        )->message('User not found');
        $v->rule(
            function ($field, $value, $params, $fields) use (&$data) {

                $authUser = $this->auth->user();

                $authUserRole = $this->userService->getById($authUser->getId());
                

                if ($authUserRole->getUserRole() !== UserRole::from('Admin')) {
                    return \false;
                }
                return true;
            },
            'name'
        )->message('You are not authorized');

        
        if (! $v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}