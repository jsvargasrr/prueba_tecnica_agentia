<?php

use App\Jobs\BuildPageEmbedding;
use App\Models\Document;
use App\Models\DocumentPage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds and stores page embedding', function () {
    $user = \App\Models\User::factory()->create();
    $doc = Document::create([
        'name' => 'Doc',
        'original_filename' => 'doc.pdf',
        'mime' => 'application/pdf',
        'size_bytes' => 123,
        'storage_path' => 'documents/doc.pdf',
        'page_count' => 1,
        'language' => 'es',
        'checksum_md5' => 'abc',
        'user_id' => $user->id,
    ]);

    $page = DocumentPage::create([
        'document_id' => $doc->id,
        'page_number' => 1,
        'content' => 'Texto de prueba para embedding',
        'embedding' => array_fill(0, config('embeddings.dim'), 0.0),
    ]);

    // Ejecuta el job (usa FakeEmbeddingService)
    (new BuildPageEmbedding($page->id))->handle(app(\App\Services\Embeddings\EmbeddingService::class));

    $page->refresh();
    // Debe haber cambiado el embedding y no ser todo ceros
    expect($page->embedding)->toBeArray();
    expect(array_sum($page->embedding))->toBeGreaterThan(0);
    expect(count($page->embedding))->toBe(8);
});
