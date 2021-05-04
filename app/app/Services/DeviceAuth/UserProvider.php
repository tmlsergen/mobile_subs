<?php

namespace App\Services\DeviceAuth;

use App\Business\DeviceManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Cache;
use Arr;

class UserProvider implements UserProviderContract
{
    private $deviceManager;

    public function __construct(DeviceManager $deviceManager)
    {
        $this->deviceManager = $deviceManager;
    }

    public function retrieveById($identifier)
    {
        return Cache::get(User::CACHE_PREFIX . $identifier);
    }

    public function retrieveByCredentials(array $credentials)
    {
        $uid = Arr::get($credentials, 'u_id');
        $appId = Arr::get($credentials, 'app_id');

        $cacheKeyWithUidAppId = User::CACHE_PREFIX . hash('sha256', $uid . $appId);

        $cachedUser = Cache::get($cacheKeyWithUidAppId);

        if(!empty($cachedUser)){
            return $cachedUser;
        }

        $deviceData = $this->deviceManager->register($credentials);
        $user = new User();

        $user->id = $deviceData->id;
        $user->uid = $deviceData->u_id;
        $user->appId = $deviceData->app_id;
        $user->operation_system = $deviceData->operating_system;
        $user->language = $deviceData->language;
        $user->callback_url = $deviceData->callback_url;
        $user->uniqueIdentifier = $deviceData->u_id.$deviceData->app_id;

        $user->setAuthPassword(hash('sha256', $appId.$uid));

        $cacheTime = now()->minutes(env('JWT_TTL'));
        Cache::put($cacheKeyWithUidAppId, $user, $cacheTime);

        $cacheKeyWithUid = User::CACHE_PREFIX . $uid.$appId;
        Cache::put($cacheKeyWithUid, $user, $cacheTime);

        return $user;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return true;
    }

    public function retrieveByToken($uid, $token)
    {
        $user = Cache::get(User::CACHE_PREFIX . $uid);

        if (!$user) {
            return;
        }

        $rememberToken = $user->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token) ? $user : null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        $user->setRememberToken($token);
        return;
    }
}
