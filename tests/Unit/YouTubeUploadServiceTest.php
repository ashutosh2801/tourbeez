<?php

namespace Tests\Unit;

use App\Services\YouTubeUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class YouTubeUploadServiceTest extends TestCase
{
    public function test_it_uploads_a_video_and_returns_youtube_urls(): void
    {
        config()->set('services.youtube', [
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'refresh_token' => 'refresh-token',
            'privacy_status' => 'unlisted',
        ]);

        Http::fakeSequence()
            ->push(['access_token' => 'access-token'])
            ->push([], 200, ['Location' => 'https://upload.youtube.test/session'])
            ->push(['id' => 'abcdefghijk']);

        $result = app(YouTubeUploadService::class)->upload(
            UploadedFile::fake()->create('niagara-tour.mp4', 100, 'video/mp4'),
            'Niagara Tour'
        );

        $this->assertSame('abcdefghijk', $result['id']);
        $this->assertSame(
            'https://www.youtube.com/watch?v=abcdefghijk',
            $result['url']
        );
        Http::assertSentCount(3);
    }

    public function test_it_requires_youtube_credentials(): void
    {
        config()->set('services.youtube', [
            'client_id' => null,
            'client_secret' => null,
            'refresh_token' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('YouTube upload is not configured');

        app(YouTubeUploadService::class)->upload(
            UploadedFile::fake()->create('tour.mp4', 10, 'video/mp4'),
            'Tour video'
        );
    }
}
