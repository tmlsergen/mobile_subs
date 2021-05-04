<?php

namespace App\Repositories;

use App\Models\Subscription;

class SubscriptionRepository
{
    private Subscription $model;

    public function __construct(Subscription $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        return $this->model->updateOrCreate(
            [
                'device_id' => $data['device_id'],
            ],
            [
                'status' => $data['status'],
                'receipt' => $data['receipt'],
                'expire_date' => $data['expire_date']
            ]);
    }

    public function updateStatus($deviceIds = [])
    {
        $query = $this->model;

        if (!empty($deviceIds)){
            $query = $query->whereIn('device_id', $deviceIds);
        }

        $query->where('expire_date', '<', now())->update(['status' => 'p']);

        return true;
    }
}
