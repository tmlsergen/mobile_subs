<?php

namespace App\Repositories;

use App\Models\Application;

class ApplicationRepository
{
    private Application $model;

    public function __construct(Application $application)
    {
        $this->model = $application;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

}
