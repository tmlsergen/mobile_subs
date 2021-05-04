<?php

namespace App\Repositories;

use App\Models\Device;

class DeviceRepository
{
    private $model;

    public function __construct(Device $model)
    {
        $this->model = $model;
    }

    public function get(array $filters = [], array $options = [])
    {
        $query = $this->model;

        if (isset($filters['id'])){
            $query = $query->where('id', $filters['id']);
        }

        if (isset($filters['u_id'])){
            $query = $query->where('u_id', $filters['u_id']);
        }

        if (isset($filters['app_id'])){
            $query = $query->where('app_id', $filters['app_id']);
        }

        if (isset($filters['language'])){
            $query = $query->where('language', $filters['language']);
        }

        if (isset($filters['operating_system'])){
            $query = $query->where('operating_system', $filters['operating_system']);
        }

        return $query->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function firstOrCreate(array $data)
    {
        return $this->model->firstOrCreate(
            [
                'app_id' => $data['app_id'],
                'u_id' => $data['u_id'],
            ],
            [
                'language' => $data['language'],
                'operating_system' => $data['operating_system']
            ]
        );
    }
}
