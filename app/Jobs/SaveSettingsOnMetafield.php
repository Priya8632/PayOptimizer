<?php

namespace App\Jobs;

use App\Http\Traits\CommonTrait;
use App\Models\PaymentCustomization;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveSettingsOnMetafield implements ShouldQueue
{
    use CommonTrait;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    public $timeout = 0;

    /**
     * Create a new job instance.
     */
    public function __construct(object $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $userSetting = Setting::where(['user_id' => $this->user->id])->get();

        $weightUnit = 'GRAMS';
        $appStatus = 'enabled';
        foreach ($userSetting as $data) {
            if ($data->slug === Setting::APP_STATUS) {
                $appStatus = $data->value;
            } else {
                $weightUnit = $data->value;
            }
        }

        $getAllCustomizationRules = PaymentCustomization::Where('user_id', $this->user->id)->whereNotNull('condition_fields')->whereNull('deleted_at')->get();

        foreach ($getAllCustomizationRules as $value) {
            $data = json_decode($value->condition_fields, true);
            if ($value->status == 'enabled') {
                $data['app_status'] = $appStatus;
                $data['weightUnit'] = $weightUnit;
                $res = $this->saveRulesOnMetafield($this->user, $value->payment_cust_id, $data);
                if (isset($res['error']) && $res['error'] == true) {
                    \Log::error(__FILE__.__LINE__.' data not save on metafields '.$value->id.'rule id. ('.$res['message'].')');
                }
            } else {
                $data['app_status'] = $appStatus;
                $data['weightUnit'] = $weightUnit;
            }
            PaymentCustomization::Where(['payment_cust_id' => $value->payment_cust_id, 'user_id' => $value->user_id])->update(['condition_fields' => $data]);
        }

    }
}
