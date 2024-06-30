<?php

namespace App\Jobs;

use App\Http\Traits\CommonTrait;
use App\Models\Customization;
use App\Models\PaymentCustomization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateRuleStatus implements ShouldQueue
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
        $getAllPaymentCustomization = PaymentCustomization::where('user_id', $this->user->id)->whereNull('deleted_at')->get();
        if (count($getAllPaymentCustomization) > 0) {
            $shopifyRulesStatusResponse = $this->getAllRulesStatus($this->user);
            if (isset($shopifyRulesStatusResponse['error']) && $shopifyRulesStatusResponse['error'] == true) {
                \Log::error(__FILE__.__LINE__.' '.$shopifyRulesStatusResponse['message']);
            } else {
                if (count($shopifyRulesStatusResponse['ruleArray']) > 0) {
                    foreach ($getAllPaymentCustomization as $key => $value) {
                        if (isset($shopifyRulesStatusResponse['ruleArray'][$value->payment_cust_id])) {

                            $status = $shopifyRulesStatusResponse['ruleArray'][$value->payment_cust_id]['status'] == 1 ? true : false;
                            $dbStatus = $shopifyRulesStatusResponse['ruleArray'][$value->payment_cust_id]['status'] == 1 ? 'enabled' : 'disabled';

                            if ($value->condition_fields != null && $dbStatus != $value->status) {
                                // \Log::info('Rule updated: ' . 'user_id ->' . $value->user_id . ' rule_id ->' . $value->id);
                                sleep(1);
                                $customization = [
                                    1 => Customization::HIDE,
                                    2 => Customization::RENAME,
                                    3 => Customization::SORT,
                                ];

                                $customizationName = $customization[$value->customization_id];
                                $changeStatusResponse = $this->changeRuleStatus($value->payment_cust_id, $status, $shopifyRulesStatusResponse['ruleArray'][$value->payment_cust_id]['title'], $this->user, $customizationName);
                                if (isset($changeStatusResponse['error']) && $changeStatusResponse['error'] == true) {
                                    \Log::error(__FILE__.__LINE__.' '.$changeStatusResponse['message']);
                                } else {
                                    PaymentCustomization::Where(['payment_cust_id' => $value->payment_cust_id, 'user_id' => $this->user->id])->update(['status' => $dbStatus]);
                                }
                            }
                        } else {
                            // \Log::info('Rule deleted: ' . 'user_id ->' . $value->user_id . ' rule_id ->' . $value->id);
                            PaymentCustomization::Where(['payment_cust_id' => $value->payment_cust_id, 'user_id' => $this->user->id])->delete();
                        }
                    }
                }
            }
        }
    }
}
