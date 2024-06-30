<?php

namespace App\Http\Controllers;

use App\Http\Traits\CommonTrait;
use App\Models\Customization;
use App\Models\PaymentCustomization;
use App\Models\Setting;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentCustomizationController extends Controller
{
    use CommonTrait;

    public function createPaymentCustomization(Request $request): JsonResponse
    {
        try {

            $sucess = false;
            $user = Auth::user();
            $client = new Client();

            $validator = Validator::make($request->all(), [
                'title' => ['required'],
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => $sucess, 'message' => $validator->errors()]);
            }

            $variables = [
                'paymentCustomization' => [
                    'enabled' => false,
                    'title' => $request->title,
                    'functionId' => $this->getFunctionId($request->customization_name),
                ],
            ];

            $createPaymentCustomization = <<<'QUERY'
                mutation paymentCustomizationCreate($paymentCustomization: PaymentCustomizationInput!) {
                    paymentCustomizationCreate(paymentCustomization: $paymentCustomization) {
                        paymentCustomization {
                            title
                            enabled,
                            functionId,
                            id
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
                            code
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
                \Log::info($responseData);
                if (isset($responseData['data']['paymentCustomizationCreate']['userErrors'])) {
                    if (isset($responseData['data']['paymentCustomizationCreate']['userErrors'][0]['message'])) {
                        return response()->json(['success' => $sucess, 'message' => 'Encountered an API error; Please retry or reach out to the app support for assistance.']);
                    }
                }
                if (isset($responseData['data']) && isset($responseData['data']['paymentCustomizationCreate'])) {
                    $tempResult = $responseData['data']['paymentCustomizationCreate'];
                    if (isset($tempResult['paymentCustomization'])) {
                        $data = $tempResult['paymentCustomization'];
                        PaymentCustomization::insert([
                            'payment_cust_id' => $data['id'],
                            'customization_id' => $request->customization_id,
                            'title' => $data['title'],
                            'status' => $data['enabled'] == 1 ? 'enabled' : 'disabled',
                            'user_id' => Auth::user()->id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                        $sucess = true;
                    }
                }
            }

        } catch (\Throwable $th) {
            return response()->json([
                'success' => $sucess,
                'message' => $th->getMessage(),
            ]);
        }

        return response()->json([
            'success' => $sucess,
        ]);
    }

    public function editPaymentCustomization(Request $request): JsonResponse
    {

        try {
            $data = $request->json()->all();

            if (! isset($data[0]['statusChangeOnDashboard'])) {

                if ($data[0]['customization'] == Customization::HIDE || $data[0]['customization'] == Customization::SORT) {

                    $errors = [];
                    foreach ($data as $key => $item) {
                        $validator = Validator::make($item, [
                            'rule_title' => ['required'],
                            'pay_methods' => ['required'],
                            'conditionFields' => ['required', 'array'],
                        ], [
                            'rule_title' => 'Title field is required',
                            'pay_methods' => 'Need to add one Payment Method',
                            'conditionFields' => 'Minimum one customization rule is required',
                        ]);

                        if ($validator->fails()) {
                            return response()->json(['success' => false, 'message' => $validator->errors()]);
                        }

                        if ($data[0]['customization'] == Customization::HIDE || $data[0]['conditionStatus'] == 'conditionally') {
                            foreach ($item['conditionFields'] as $dataIndex => $field) {
                                $validator = Validator::make($field, [
                                    'cartDetails' => [
                                        function ($attribute, $value, $fail) use ($field) {
                                            if (in_array($field['ruleType'], config('constants.cart')) && empty($value)) {
                                                $fail($field['ruleType'].' '.'value is required');
                                            }
                                        }],
                                    'customerDetails' => [
                                        function ($attribute, $value, $fail) use ($field) {
                                            if (in_array($field['ruleType'], config('constants.customer')) && empty($value)) {
                                                $fail($field['ruleType'].' '.'value is required');
                                            }
                                        }],
                                    'countries' => [
                                        function ($attribute, $value, $fail) use ($field) {
                                            if (in_array($field['ruleType'], config('constants.country')) && empty($value)) {
                                                $fail($field['ruleType'].' '.'value  is required');
                                            }
                                        }],
                                    'selectedCollectionIds' => [
                                        function ($attribute, $value, $fail) use ($field) {
                                            if (in_array($field['ruleType'], config('constants.collections')) && empty($value)) {
                                                $fail($field['ruleType'].' '.'value  is required');
                                            }
                                        }],
                                ]);
                                if ($validator->fails()) {
                                    $errors[] = $validator->errors();
                                }

                            }
                        }
                    }

                    if (count($errors) > 0) {
                        return response()->json(['success' => false, 'message' => $errors]);
                    }
                } else {
                    $errors = [];
                    foreach ($data as $key => $item) {
                        $validator = Validator::make($item, [
                            'rule_title' => ['required'],
                            'conditionFields' => ['required', 'array'],
                            'conditionFields.*.methods' => ['required', 'array'],
                        ], [
                            'rule_title' => 'Title field is required',
                            'conditionFields' => 'Minimum one customization rule is required',
                            'conditionFields.*.methods' => 'Minimum one payment method is required',
                        ]);

                        if ($item['conditionStatus'] == 'country') {
                            $validator->addRules([
                                'conditionFields.*.countries' => ['required', 'array'],
                            ]);
                        } elseif ($item['conditionStatus'] == 'language') {
                            $validator->addRules([
                                'conditionFields.*.languages' => ['required', 'array'],
                            ]);
                        } elseif ($item['conditionStatus'] == 'customer-tags') {
                            $validator->addRules([
                                'conditionFields.*.customerTags' => ['required', 'array'],
                            ]);
                        }
                        if ($validator->fails()) {
                            return response()->json(['success' => false, 'message' => $validator->errors()]);
                        }

                        foreach ($item['conditionFields'] as $dataIndex => $field) {
                            $methods = [];
                            foreach ($field['methods'] as $key => $value) {
                                $validator = Validator::make($value, [
                                    'old_method' => ['required'],
                                    'new_method' => ['required'],
                                ], [
                                    'old_method' => 'Field is required',
                                    'new_method' => 'Field is required',
                                ]);

                                if ($validator->fails()) {
                                    $errors[] = $validator->errors();
                                }

                                if (isset($value['old_method'])) {
                                    $method = $value['old_method'];

                                    if (in_array($method, $methods)) {
                                        return response()->json(['message' => 'Payment method must be unique.', 'success' => false]);
                                    } else {
                                        $methods[] = $method;
                                    }
                                }
                            }
                        }
                    }
                    if (count($errors) > 0) {
                        return response()->json(['success' => false, 'message' => $errors]);
                    }
                }
            }

            $user = Auth::user();
            $client = new Client();

            $paymentCustId = 'gid://shopify/PaymentCustomization/'.$data[0]['payment_cust_id'];
            $status = $data[0]['rule_status'] == 'enabled' ? true : false;

            $getPaymentcustomization = PaymentCustomization::where(['payment_cust_id' => $paymentCustId])->first();
            $conditions = $getPaymentcustomization->condition_fields;

            if (empty($conditions) && isset($data[0]['statusChangeOnDashboard'])) {
                $message = 'You can not activate the status before set customization rule';

                return response()->json(['success' => false, 'message' => $message]);
            }

            $customization = [
                1 => Customization::HIDE,
                2 => Customization::RENAME,
                3 => Customization::SORT,
            ];

            $customizationName = $customization[$getPaymentcustomization->customization_id];
            $res = $this->changeRuleStatus($paymentCustId, $status, $data[0]['rule_title'], $user, $customizationName);
            if (isset($res['error']) && $res['error'] == true) {
                if (! isset($res['limitReached'])) {
                    \Log::error(__FILE__.__LINE__.' '.$res['message']);
                }

                return response()->json(['success' => false, 'message' => $res['message'], 'limitReached' => $res['limitReached']]);
            }

            if (! isset($data[0]['statusChangeOnDashboard'])) {

                $userSettings = Setting::appstatus($user->id)->first();
                $selectedCollectionIds = [];
                foreach ($data[0]['conditionFields'] as $items) {
                    if (! empty($items['selectedCollectionIds'])) {
                        $selectedCollectionIds = array_merge($selectedCollectionIds, $items['selectedCollectionIds']);
                    }
                }
                $tags = [];
                foreach ($data[0]['conditionFields'] as $items) {
                    if ($data[0]['customization'] == Customization::HIDE && $items['ruleType'] == 'customer-tags') {
                        if (! empty($items['customerDetails'])) {
                            $tags = array_merge($tags, $items['customerDetails']);
                        }
                    }
                    if (! empty($items['customerTags'])) {
                        $tags = array_merge($tags, $items['customerTags']);
                    }
                }
                $conditions = [
                    'customization' => $data[0]['customization'], // hide.rename,sort
                    'app_status' => $userSettings->value,
                    'weightUnit' => $data[0]['weightUnit'], // global weightunit
                ];

                if ($data[0]['customization'] == Customization::HIDE) {
                    $conditions += [
                        'paymentMethods' => $data[0]['pay_methods'], // payment-methods array
                        'conditionStatus' => $data[0]['conditionStatus'], // hide,show
                        'operator' => $data[0]['operator'], // and,or
                        'fields' => $data[0]['conditionFields'], // rules
                        'selectedCollectionIds' => $selectedCollectionIds, // collections format
                        'tags' => $tags, // customer_tags format
                    ];
                } elseif ($data[0]['customization'] == Customization::RENAME) {
                    $conditions += [
                        'conditionStatus' => $data[0]['conditionStatus'], // always,country,customer_tags
                        'fields' => $data[0]['conditionFields'], // rules
                        'tags' => $tags, // customer_tags format
                    ];
                } elseif ($data[0]['customization'] == Customization::SORT) {
                    $conditions += [
                        'conditionStatus' => $data[0]['conditionStatus'], // conditionally,always
                        'paymentMethods' => $data[0]['pay_methods'], // payment-methods array
                        'fields' => $data[0]['conditionFields'], // rules
                        'sortingValue' => $data[0]['sortingValue'], // asc,desc
                    ];
                }

                $res = $this->saveRulesOnMetafield($user, $paymentCustId, $conditions);
                if (isset($res['error']) && $res['error'] == true) {
                    \Log::error(__FILE__.__LINE__.' data not save on metafields '.$getPaymentcustomization->id.' rule id. ('.$res['message'].')');

                    return response()->json(['success' => false, 'message' => $res['message']]);
                }

            }

            $getPaymentcustomization->payment_cust_id = $paymentCustId;
            $getPaymentcustomization->title = $data[0]['rule_title'];
            $getPaymentcustomization->status = $data[0]['rule_status'];
            $getPaymentcustomization->condition_fields = $conditions;
            $getPaymentcustomization->save();

            $message = 'Rule saved';
            if (isset($data[0]['statusChangeOnDashboard'])) {
                $message = 'Status updated';
            }

        } catch (\Exception $th) {
            \Log::error($th->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something Wrong',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function getAllPaymentCustomizations(Request $request): JsonResponse
    {
        $query = PaymentCustomization::with([
            'customization' => function ($q) {
                $q->select('id', 'name');
            },
        ])->Where(['user_id' => Auth::user()->id])->whereNull('deleted_at');

        if ($request->status == 1) {
            $query->where('status', 'enabled');
        }

        $searchString = '';
        if (isset($request->search) && ! empty($request->search)) {
            $searchString = $request->search;

            $query->where(function ($q) use ($searchString) {
                $q->where('title', 'LIKE', "%$searchString%")
                    ->orWhereHas('customization', function ($query) use ($searchString) {
                        $query->where('name', 'like', $searchString.'%');
                    });
            });
        }

        if ($request->limit) {
            $query->limit($request->limit)->offset($request->offset);
        }
        $data = $query->get();

        if (! empty($searchString)) {
            $totalQuery = PaymentCustomization::with([
                'customization' => function ($q) {
                    $q->select('id', 'name');
                },
            ])->Where(['user_id' => Auth::user()->id])
                ->where(function ($q) use ($searchString) {
                    $q->where('title', 'LIKE', "%$searchString%")
                        ->orWhereHas('customization', function ($query) use ($searchString) {
                            $query->where('name', 'like', $searchString.'%');
                        });
                })
                ->whereNull('deleted_at')
                ->Where(['user_id' => Auth::user()->id])
                ->count();
        } else {

            $q = PaymentCustomization::with([
                'customization' => function ($q) {
                    $q->select('id', 'name');
                },
            ])->Where(['user_id' => Auth::user()->id])->whereNull('deleted_at');

            if ($request->status == 1) {
                $totalQuery = $q->where('status', 'enabled')->count();
            } else {
                $totalQuery = $q->count();
            }
        }

        $totalActiveCustomizationCount = PaymentCustomization::active(Auth::user()->id)->count();

        $totalHidePayments = PaymentCustomization::hide(Auth::user()->id)->count();
        $totalRenamePayments = PaymentCustomization::rename(Auth::user()->id)->count();
        $totalSortPayments = PaymentCustomization::sort(Auth::user()->id)->count();
        $hideEnableCount = PaymentCustomization::hideenable(Auth::user()->id)->count();
        $sortEnableCount = PaymentCustomization::sortenable(Auth::user()->id)->count();
        $renameEnableCount = PaymentCustomization::renameenable(Auth::user()->id)->count();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $totalQuery,
            'active' => $totalActiveCustomizationCount,
            'hide' => $totalHidePayments,
            'rename' => $totalRenamePayments,
            'sort' => $totalSortPayments,
            'hide_enable' => $hideEnableCount,
            'sort_enable' => $sortEnableCount,
            'rename_enable' => $renameEnableCount,
        ]);
    }

    public function deletePaymentCustomization($id): JsonResponse
    {
        $paymentCustId = PaymentCustomization::find($id);
        try {

            $user = Auth::user();
            $client = new Client();

            $variables = [
                'id' => $paymentCustId->payment_cust_id,
            ];
            $deletePaymentCustomization = <<<'QUERY'
            mutation paymentCustomizationDelete($id: ID!) {
                paymentCustomizationDelete(id: $id) {
                deletedId
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
                    'query' => $deletePaymentCustomization,
                    'variables' => $variables,
                ],
            ]);
            if ($responseMetadata->getStatusCode() == 200) {

                $responseData = json_decode($responseMetadata->getBody()->getContents(), true);
                if (isset($responseData['data']['metafieldsSet']['userErrors'][0]['message'])) {
                    $message = 'API Error';

                    return response()->json(['success' => false, 'message' => $message]);
                }
                PaymentCustomization::where('id', $id)->delete();
                // Log::info($responseData);
            }
        } catch (\Throwable $th) {
            // \Log::error($th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something Wrong',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Customization removed',
        ]);

    }

    public function getPaymentCustomization($id): JsonResponse
    {
        $id = 'gid://shopify/PaymentCustomization/'.$id;
        $data = PaymentCustomization::with([
            'customization' => function ($q) {
                $q->select('id', 'name');
            },
        ])
            ->where(['payment_cust_id' => $id])->first();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getPaymentMethods(): JsonResponse
    {
        $paymentMethodsList = [];
        try {

            $user = Auth::user();
            $client = new Client();

            $getPaymentMethods = <<<'QUERY'
                        {
                            translatableResources(first: 10, resourceType: PAYMENT_GATEWAY) {
                            nodes {
                                translatableContent {
                                key
                                value
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
                    'query' => $getPaymentMethods,
                ],
            ]);

            if ($responseMetadata->getStatusCode() == 200) {
                $responseData = json_decode($responseMetadata->getBody()->getContents(), true);
                if (isset($responseData['data']) && isset($responseData['data']['translatableResources'])) {
                    $tempResult = $responseData['data']['translatableResources'];
                    foreach ($tempResult['nodes'] as $key => $item) {
                        if (isset($item['translatableContent']) && count($item['translatableContent']) > 0) {
                            $paymentMethodsList[] = $item['translatableContent'][0]['value'];
                        }
                    }
                    // Log::info($paymentMethodsList);
                }
            }
        } catch (\Throwable $th) {
            \Log::error($th->getMessage());

        }

        return response()->json([
            'success' => true,
            'methods' => $paymentMethodsList,
        ]);
    }

    public function getCountryList(): JsonResponse
    {
        $countries = [];
        try {
            $user = Auth::user();
            $data = $user
                ->api()
                ->rest(
                    'GET',
                    '/admin/api/'.
                    config('shopify-app.api_version').
                    '/countries.json'
                );
            if ($data['status'] == 200) {
                if (isset($data['body'])) {
                    $tempResult = $data['body']['container']['countries'];
                    foreach ($tempResult as $key => $value) {
                        $countries[] = ['country' => $value['name'], 'code' => $value['code']];
                    }
                    // \Log::info(json_encode($countries));
                }
            }
        } catch (\Throwable $th) {
            \Log::error($th->getMessage());
        }

        return response()->json([
            'success' => true,
            'countries' => $countries,
        ]);
    }

    public function getLanguageList(): JsonResponse
    {
        $languageList = [];
        try {

            $user = Auth::user();
            $client = new Client();

            $getLanguageList = <<<'QUERY'
                    query {
                        availableLocales {
                            isoCode
                            name
                        }
                    }
                    QUERY;

            $responseMetadata = $client->request('POST', "https://{$user->name}/admin/api/".config('shopify-app.api_version').'/graphql.json', [
                'headers' => [
                    'X-Shopify-Access-Token' => "{$user->password}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'query' => $getLanguageList,
                ],
            ]);

            if ($responseMetadata->getStatusCode() == 200) {
                $responseData = json_decode($responseMetadata->getBody()->getContents(), true);
                // \Log::info($responseData);
                if (isset($responseData['data']) && isset($responseData['data']['availableLocales'])) {
                    $tempResult = $responseData['data']['availableLocales'];
                    foreach ($tempResult as $key => $item) {
                        $languageList[] = $item;
                    }
                    // \Log::info($languageList);
                }
            }
        } catch (\Throwable $th) {
            \Log::error($th->getMessage());

        }

        return response()->json([
            'success' => true,
            'languages' => $languageList,
        ]);
    }
}
