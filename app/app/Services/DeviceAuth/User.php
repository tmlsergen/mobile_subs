<?php

namespace App\Services\DeviceAuth;

use Illuminate\Contracts\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Arr;
use Cache;

class User implements Authenticatable, JWTSubject
{
    const CACHE_PREFIX = 'device_user';

    public $id;
    public $uniqueIdentifier;
    public $uid;
    public $appId;
    protected $password;
    public $language;
    public $operation_system;
    public $callback_url;
    public $remember_token;

    public function getAuthIdentifierName()
    {
        return 'uniqueIdentifier';
    }

    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    public function getAuthPassword()
    {
        return $this->password;
    }


    public function setAuthPassword($password)
    {
        $this->password = $password;
    }

    public function getRememberToken()
    {
        return $this->remember_token;
    }

    public function setRememberToken($value)
    {
        $this->remember_token = $value;

        Cache::put(self::CACHENAME.$this->username, $this);
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->uid.$this->appId;
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
