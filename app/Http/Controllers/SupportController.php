<?php

namespace App\Http\Controllers;

use App\Mail\supportAdminMail;
use App\Mail\supportUserMail;
use App\Models\Support;
use App\Models\User;
use App\Notifications\SupportNotification;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SupportController extends Controller
{
    public function saveSupport(Request $request): JsonResponse
    {
        $user = Auth::user();
        $success = false;
        $message = 'Something went wrong';

        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required|min:5',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors(), 'success' => $success]);
        } else {

            $support = Support::create([
                'user_id' => $user->id,
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'message' => $request->input('message'),
            ]);

            if (env('APP_ENV', 'local') === 'production' && ! empty(env('SLACK_NOTIFICATION_URL'))) {
                $user->notify(new SupportNotification($user, $support));
            }

            try {
                Mail::to($request->input('email'))->queue(new supportUserMail($support)); // new support user mail
                Mail::to(env('MAIL_FROM_ADDRESS'))->queue(new supportAdminMail($support, $user)); // new support admin mail
            } catch (Exception $e) {
                \Log::error($e->getMessage());
            }

            $success = true;
            $message = 'Support save successfully';

        }

        return response()->json(['message' => $message, 'success' => $success]);

    }
}
