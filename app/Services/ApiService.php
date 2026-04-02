<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class ApiService
{
    public function request($method, $url, $data = [], $headers = [])
    {
        $response = Http::withHeaders($headers)->$method($url, $data);

        if ($response->successful()) {
            return $response->json();
        }

        return [
            'error' => true,
            'status' => $response->status(),
            'message' => $response->body()
        ];
    }
}