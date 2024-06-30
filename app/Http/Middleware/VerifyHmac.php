<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyHmac
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hmac_header = $request->header('x-shopify-hmac-sha256')??null;
        $data = $request->getContent();
        
        $verified = $this->verify_webhook($data, $hmac_header);
        
        if($verified){
            return $next($request);
        }else{
            return response()->json(['error' => 'Invalid HMAC signature'], 401);
        }
    }
    public function verify_webhook($data, $hmac_header)
    {
        $calculated_hmac = base64_encode(hash_hmac('sha256', $data, env('SHOPIFY_API_SECRET'), true));
        return hash_equals($calculated_hmac, $hmac_header);
    }
}
