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


        $payload = json_decode($request->getContent(), true); // or your JSON string

        $messageId = $payload['event-data']['message']['headers']['message-id'] ?? null;
        $recipient = $payload['event-data']['recipient'] ?? null;


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
