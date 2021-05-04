<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App;

class Device extends Model
{
    use HasFactory;

    protected $fillable =[
        'app_id',
        'u_id',
        'language',
        'operating_system',
        'callback_url'
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public static function boot()
    {
        parent::boot();

        self::created(function ($model){
            $esService = App::make(App\Services\DeviceSearchService::class);
            $esService->index($model);
        });
    }
}
