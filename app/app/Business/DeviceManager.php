<?php

namespace App\Business;

use App\Repositories\DeviceRepository;

class DeviceManager
{
    private DeviceRepository $deviceRepository;

    public function __construct(DeviceRepository $deviceRepository)
    {
        $this->deviceRepository = $deviceRepository;
    }

    public function register(array $credentials)
    {
        return $this->deviceRepository->firstOrCreate($credentials);
    }
}
