<?php

namespace App\Business;

class AuthManager
{
    public function getAuthGuard()
    {
        return auth('device');
    }

    public function register(array $data)
    {
        return $this->getAuthGuard()->attempt($data);
    }
}
