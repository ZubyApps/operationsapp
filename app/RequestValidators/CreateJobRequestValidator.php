<?php

declare(strict_types = 1);

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exception\ValidationException;
use App\Services\ClientService;
use App\Services\JobService;
use App\Services\JobTypeService;
use DateTime;
use Doctrine\ORM\EntityManager;
use Valitron\Validator;
use DoctrineExtensions\Query\Mysql\Date;

class CreateJobRequestValidator implements RequestValidatorInterface
{
    public function __construct(
        private readonly ClientService $clientService, 
        private readonly JobTypeService $jobTypeService,
        private readonly JobService $jobService,
        private readonly EntityManager $entityManager
        )
    {
    }

    public function validate(array $data): array
    {
        $v = new Validator($data);

        $v->rule('required', 'client')->message('Please pick a Client from the list');
        $v->rules(['required' => [ ['jobType'], ['jobStatus'],['dueDate', true] ] ]);
        $v->rule('required', 'bill')->message('Please check this value');
        $v->rule('required', 'details')->message('Details are required');
        $v->rule('lengthMax', 'details', 2000);
        $v->rule('integer', 'client');
        $v->rule(
            function ($field, $value, $params, $fields) use (&$data) {
                $id = (int) explode(' ', $value)[0];

                if (!$id) {return false;}

                $client = $this->clientService->getById($id);

                if ($client) {$data['client'] = $client;
                                return \true;
                                }
                                return \false;
                                },
                                'client')->message('Client not found');
        $v->rule(
            function ($field, $value, $params, $fields) use (&$data) {
                $id = (int) $value;

                if (!$id) {return false;}

                $jobType = $this->jobTypeService->getById($id);

                if ($jobType) {$data['jobType'] = $jobType;
                        return \true;
                    }
                    return \false;
                },
                'jobType'
        )->message('JobType not set');
        $v->rule(
            function ($field, $value, $params, $fields) use (&$data) {

                if ($data['jobStatus'] === 'Booked' && $value === '') {
                    return false;
                } 
                return true; 
                },
                'dueDate'
        )->message('A booked job must have a due date');
        $v->rule(
            function ($field, $value, $params, $fields) use (&$data) {

                if ($value === '') {
                    return \true;
                }

                $dateToBook = explode('T', $value)[0];
                $today = (new DateTime())->format('Y-m-d');

                $checkDay = $this->entityManager
                    ->createQuery("SELECT Date(j.dueDate) FROM App\Entity\Job j where Date(j.dueDate) = :booked AND Date(j.dueDate) > :today")
                ->setParameters([
                    'booked'=> $dateToBook,
                    'today' => $today
                    ])->getArrayResult();
                    
                    if (count($checkDay) > 0 && $data['forceBooking'] === '') {
                        return false;
                    } elseif (count($checkDay) > 0 && $data['forceBooking'] !== '') {
                        return true;
                    }
                return true;
            },
            'dueDate'
        )->message('Warning: There is an event on this day');

        if (! $v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}