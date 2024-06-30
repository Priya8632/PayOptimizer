<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class WebHookController extends Controller
{
    public function customerRedact(){
        \Log::info('customer data delete webhook call');
        return response()->json(["message"=>"we are not save any customer and order data"],200);
    }

    public function customerRequest(){
        \Log::info('customer data get Request webhook call');
        return response()->json(["message"=>"we are not save any customer and order data"],200);
    }

    public function shopRedact(Request $request){
        \Log::info('shopRedact delete request webhook call');
        if($request->has('shop_domain')){
            $shopDomain = $request->get('shop_domain');
            \Log::info('shop domain ->'.$shopDomain);

            $deletedUser = User::where(['name'=>$shopDomain])->whereNull('password')->first();
            if(!empty($deletedUser)){
                return response()->json(["message"=>"App still not uninstall or invalid shop domain so we can not delete shop data"],200);    
            }else{
                return response()->json(["message"=>"data deleted successfully"],200);
            }
        }else{
            return response()->json(["message"=>"shop domain is invalid or missing"],200);    
        }
    }
}
