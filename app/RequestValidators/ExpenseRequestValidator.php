<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exception\ValidationException;
use App\Services\CategoryService;
use App\Services\SponsorService;
use Doctrine\ORM\EntityManager;
use Valitron\Validator;

class ExpenseRequestValidator implements RequestValidatorInterface
{
    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly CategoryService $categoryService,
        private readonly SponsorService $sponsorService,
    ) {
    }

    public function  validate(array $data): array
    {

        $v = new Validator($data);

        $v->rule('required', ['category', 'sponsor', 'description']);
        $v->rule('integer', ['category', 'sponsor']);
        $v->rule('numeric', 'amount')->message('Please check this value');
        $v->rule(
            function ($field, $value, $params, $fields) use (&$data) {
                $id = (int) $value;

                if (!$id) {
                    return false;
                }

                $category = $this->categoryService->getById($id);

                if ($category) {
                    $data['category'] = $category;
                    return \true;
                }
                return \false;
            },
            'category'
        )->message('Category not set');
        $v->rule(
            function ($field, $value, $params, $fields) use (&$data) {
                $id = (int) $value;

                if (!$id) {
                    return false;
                }

                $sponsor = $this->sponsorService->getById($id);

                if ($sponsor) {
                    $data['sponsor'] = $sponsor;
                    return \true;
                }
                return \false;
            },
            'sponsor'
        )->message('Sponsor not set');

        if (!$v->validate()) {
            throw new ValidationException($v->errors());
        }
        
        return $data;
    }
}