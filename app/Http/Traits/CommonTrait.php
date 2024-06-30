<?php

namespace App\Http\Traits;

use App\Models\Customization;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

trait CommonTrait
{
    public function saveRulesOnMetafield($user, $paymentCustId, $data): array
    {

        $client = new Client();
        try {
            $variables = [
                'metafields' => [
                    'key' => env('KEY'),
                    'namespace' => '$app:'.env('NAMESPACE'),
                    'ownerId' => "$paymentCustId",
                    'type' => 'json',
                    'value' => json_encode($data),
                ],
            ];

            $createPaymentCustomization = <<<'QUERY'
                mutation metafieldsSet($metafields: [MetafieldsSetInput!]!) {
                metafieldsSet(metafields: $metafields) {
                    metafields {
                        namespace
                        key
                        type
                        value
                        id
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
            QUERY;

            $responseMetadata = $client->request('POST', "https://{$user->name}/admin/api/".config('shopify-app.api_version').'/graphql.json', [
                'headers' => [
                    'X-Shopify-Access-Token' => "{$user->password}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'query' => $createPaymentCustomization,
                    'variables' => $variables,
                ],
            ]);
            if ($responseMetadata->getStatusCode() == 200) {
                $responseData = json_decode($responseMetadata->getBody()->getContents(), true);
                // \Log::info($responseData);

                if (isset($responseData['data']['metafieldsSet']['metafields']) && count($responseData['data']['metafieldsSet']['metafields']) > 0) {
                    // success
                    return ['error' => false];
                } else {
                    // something wrong
                    $message = 'Encountered an API error; Please retry or reach out to the app support for assistance.';
                    if (isset($responseData['data']['metafieldsSet']['userErrors'][0]['message'])) {
                        $message = 'API Error :- '.$responseData['data']['metafieldsSet']['userErrors'][0]['message'];
                    }

                    return ['error' => true, 'message' => $message];
                }
            }

        } catch (Exception $e) {

            Log::error(__FILE__.__LINE__.$e->getMessage());

            return ['error' => true, 'message' => 'Encountered an API error; Please retry or reach out to the app support for assistance.'];
        }

        return ['error' => true, 'message' => 'Encountered an API error; Please retry or reach out to the app support for assistance.'];
    }

    public function getAllRulesStatus($user): array
    {

        $client = new Client();
        try {

            $allPaymentCustomization = <<<'QUERY'
            {
                paymentCustomizations(first:250) {
                    edges{
                        node {
                            id
                            enabled
                            title
                        }
                    }
                }
            }
            QUERY;

            $responseMetadata = $client->request('POST', "https://{$user->name}/admin/api/".config('shopify-app.api_version').'/graphql.json', [
                'headers' => [
                    'X-Shopify-Access-Token' => "{$user->password}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'query' => $allPaymentCustomization,
                ],
            ]);
            if ($responseMetadata->getStatusCode() == 200) {

                $responseData = json_decode($responseMetadata->getBody()->getContents(), true);
                // \Log::info($responseData);
                $rulesArray = [];
                if (isset($responseData['data']) && isset($responseData['data']['paymentCustomizations'])) {
                    $tempResult = $responseData['data']['paymentCustomizations'];
                    if (isset($tempResult['edges']) && count($tempResult['edges']) > 0) {
                        foreach ($tempResult['edges'] as $key => $item) {
                            if (isset($item['node']) && count($item['node']) > 0) {
                                $rulesArray[$item['node']['id']] = ['status' => $item['node']['enabled'], 'title' => $item['node']['title']];
                            }
                        }
                    }
                }

                // \Log::info($rulesArray);
                return ['error' => false, 'ruleArray' => $rulesArray];
            }
        } catch (\Throwable $th) {
            Log::error(__FILE__.__LINE__.$th->getMessage());

            return ['error' => true, 'message' => 'Encountered an API error; Please retry or reach out to the app support for assistance.'];
        }

        return ['error' => true, 'message' => 'Encountered an API error; Please retry or reach out to the app support for assistance.'];
    }

    public function changeRuleStatus($paymentCustId, $status, $title, $user, $paymentCustName): array
    {
        $client = new Client();

        try {

            $paymentCustomization = [
                'paymentCustId' => $paymentCustId,
                'paymentCustomization' => [
                    'enabled' => $status,
                    'title' => $title,
                    'functionId' => $this->getFunctionId($paymentCustName),
                ],
            ];

            $updatePaymentCustomization = <<<'QUERY'
            mutation paymentCustomizationUpdate($paymentCustId: ID!, $paymentCustomization: PaymentCustomizationInput!) {
                paymentCustomizationUpdate(id: $paymentCustId, paymentCustomization: $paymentCustomization) {
                  paymentCustomization {
                    id
                    title
                    functionId,
                    enabled
                    errorHistory{
                        errorsFirstOccurredAt
                        firstOccurredAt
                        hasBeenSharedSinceLastError
                        hasSharedRecentErrors
                    }
                  }
                  userErrors {
                    field
                    message
                  }
                }
              }
            QUERY;

            $responseMetadata = $client->request('POST', "https://{$user->name}/admin/api/".config('shopify-app.api_version').'/graphql.json', [
                'headers' => [
                    'X-Shopify-Access-Token' => "{$user->password}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'query' => $updatePaymentCustomization,
                    'variables' => $paymentCustomization,
                ],
            ]);
            if ($responseMetadata->getStatusCode() == 200) {
                $responseData = json_decode($responseMetadata->getBody()->getContents(), true);
                // \Log::info($responseData);
                if (isset($responseData['data']['paymentCustomizationUpdate']['userErrors'])) {
                    if (isset($responseData['data']['paymentCustomizationUpdate']['userErrors'][0]['message'])) {
                        $message = 'Could not enable payment customization.';

                        return ['error' => true, 'message' => $message, 'limitReached' => true];
                    }
                }

                if (isset($responseData['data']) && isset($responseData['data']['paymentCustomizationUpdate'])) {
                    $tempResult = $responseData['data']['paymentCustomizationUpdate'];
                    if (isset($tempResult['paymentCustomization'])) {
                        $data = $tempResult['paymentCustomization'];

                        return ['error' => false, 'ruleArray' => $data];
                    }
                }
            }
        } catch (\Throwable $th) {
            Log::error(__FILE__.__LINE__.$th->getMessage());

            return ['error' => true, 'message' => 'Encountered an API error; Please retry or reach out to the app support for assistance.'];
        }

        return ['error' => true, 'message' => 'Encountered an API error; Please retry or reach out to the app support for assistance.'];

    }

    public function getFunctionId($paymentCustName): string
    {
        if ($paymentCustName == Customization::HIDE) {
            return env('HIDE_FUNCTION_ID');
        } elseif ($paymentCustName == Customization::RENAME) {
            return env('RENAME_FUNCTION_ID');
        } elseif ($paymentCustName == Customization::SORT) {
            return env('SORT_FUNCTION_ID');
        }

        return '';
    }
}
