<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class YouTubeUploadService
{
    public function upload(UploadedFile $file, string $title, ?string $description = null): array
    {
        $accessToken = $this->accessToken();
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $fileSize = $file->getSize();

        $initResponse = $this->authorized($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json; charset=UTF-8',
                'X-Upload-Content-Length' => $fileSize,
                'X-Upload-Content-Type' => $mimeType,
            ])
            ->post(
                'https://www.googleapis.com/upload/youtube/v3/videos?uploadType=resumable&part=snippet,status',
                [
                    'snippet' => [
                        'title' => mb_substr($title, 0, 100),
                        'description' => $description ?: '',
                    ],
                    'status' => [
                        'privacyStatus' => config('services.youtube.privacy_status', 'unlisted'),
                        'selfDeclaredMadeForKids' => false,
                    ],
                ]
            );

        if (!$initResponse->successful() || !$initResponse->header('Location')) {
            throw new RuntimeException($this->errorMessage(
                $initResponse->json(),
                'YouTube did not create an upload session.'
            ));
        }

        $stream = fopen($file->getRealPath(), 'rb');
        if ($stream === false) {
            throw new RuntimeException('The uploaded video could not be opened.');
        }

        try {
            $uploadResponse = $this->authorized($accessToken)
                ->withHeaders([
                    'Content-Length' => $fileSize,
                ])
                ->withBody($stream, $mimeType)
                ->put($initResponse->header('Location'));
        } finally {
            fclose($stream);
        }

        if (!$uploadResponse->successful() || !$uploadResponse->json('id')) {
            throw new RuntimeException($this->errorMessage(
                $uploadResponse->json(),
                'YouTube video upload failed.'
            ));
        }

        $videoId = $uploadResponse->json('id');

        return [
            'id' => $videoId,
            'url' => "https://www.youtube.com/watch?v={$videoId}",
            'medium_url' => "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg",
            'thumb_url' => "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg",
        ];
    }

    private function accessToken(): string
    {
        $clientId = config('services.youtube.client_id');
        $clientSecret = config('services.youtube.client_secret');
        $refreshToken = config('services.youtube.refresh_token');

        if (!$clientId || !$clientSecret || !$refreshToken) {
            throw new RuntimeException(
                'YouTube upload is not configured. Set YOUTUBE_CLIENT_ID, YOUTUBE_CLIENT_SECRET and YOUTUBE_REFRESH_TOKEN.'
            );
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if (!$response->successful() || !$response->json('access_token')) {
            throw new RuntimeException($this->errorMessage(
                $response->json(),
                'YouTube authentication failed.'
            ));
        }

        return $response->json('access_token');
    }

    private function authorized(string $accessToken): PendingRequest
    {
        return Http::withToken($accessToken)
            ->acceptJson()
            ->timeout(600)
            ->connectTimeout(30);
    }

    private function errorMessage(mixed $payload, string $fallback): string
    {
        return data_get($payload, 'error.message')
            ?? data_get($payload, 'error_description')
            ?? $fallback;
    }
}
