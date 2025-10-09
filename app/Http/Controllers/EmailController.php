<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderEmailHistory;
use Illuminate\Support\Facades\Log;

class EmailController extends Controller
{

    public function handle($event, Request $request)
    {
        $data = $request->all();
        \Log::info("Mailgun Event: $event", $data);
        // $payload = $request->all();



        // $messageId = $payload['Message-Id'] ?? $payload['message']['headers']['message-id'] ?? null;
        // $recipient = $payload['recipient'] ?? null;


        $payload = json_decode($request->getContent(), true); // or your JSON string

        $messageId = $payload['event-data']['message']['headers']['message-id'] ?? null;
        $recipient = $payload['event-data']['recipient'] ?? null;

         \Log::info("Mailgun Event: $messageId ------ $recipient", );




        if ($messageId && $recipient) {
            $history = OrderEmailHistory::where('message_id', $messageId)
                ->where('to_email', $recipient)
                ->first();
            // \Log::info("Mailgun history: $history");
            if ($history) {
                $history->update(['status' => $event]);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
