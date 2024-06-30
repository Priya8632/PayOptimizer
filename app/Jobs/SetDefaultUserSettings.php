<?php

namespace App\Jobs;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetDefaultUserSettings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    public $timeout = 0;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $userSetting = Setting::where(['user_id' => $this->user->id])->get();
        if ($userSetting->isEmpty()) {
            Setting::insert([
                [
                    'slug' => Setting::WEIGHT_UNIT,
                    'value' => 'GRAMS',
                    'user_id' => $this->user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'slug' => Setting::APP_STATUS,
                    'value' => 'enabled',
                    'user_id' => $this->user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

    }
}
