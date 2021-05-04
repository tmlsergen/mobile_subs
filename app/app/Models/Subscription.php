<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'status',
        'receipt',
        'expire_date'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    protected static function boot()
    {
        parent::boot();

        self::created(function ($model){
            App\Events\SubscriptionStarted::dispatch();
            $esService = App::make(App\Services\SubscriptionSearchService::class);
            $esService->index($model);
        });

        self::updated(function ($model){
            if ($model->status == 'p'){
                App\Events\SubscriptionCanceled::dispatch();
            }else {
                App\Events\SubscriptionReNewed::dispatch();
            }

            $esService = App::make(App\Services\SubscriptionSearchService::class);
            $esService->index($model);
        });
    }
}
