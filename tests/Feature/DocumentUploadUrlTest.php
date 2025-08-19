<?php

use App\Jobs\DownloadAndIngestDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('queues a URL ingestion job and processes it', function () {
    Queue::fake();

    $user = \App\Models\User::factory()->create();
    $token = auth()->login($user);

    // Encola
    $res = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/documents/upload-url', [
            'url' => 'https://example.com/fake.pdf',
            'name' => 'PDF por URL',
        ]);

    $res->assertStatus(202)->assertJson(['queued' => true]);
    Queue::assertPushed(DownloadAndIngestDocument::class);

    // Finge descarga PDF válida (%PDF al inicio)
    Http::fake([
        'https://api-inference.huggingface.co/*' => Http::response([], 200), // por si acaso
        'https://example.com/fake.pdf' => Http::response("%PDF-1.4\nbody...", 200),
    ]);

    // Ejecuta el Job inline
    $job = new DownloadAndIngestDocument($user->id, 'https://example.com/fake.pdf', 'PDF por URL');
    $job->handle(app(\App\Services\PdfService::class)); // PdfService real, pero podemos mockearlo:


    // Verifica
    $doc = \App\Models\Document::where('name', 'PDF por URL')->first();
    expect($doc)->not->toBeNull();
    expect($doc->page_count)->toBeGreaterThan(0);
});
