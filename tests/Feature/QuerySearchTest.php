<?php

use App\Models\Document;
use App\Models\DocumentPage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns semantic search results', function () {
    // Crea documento y páginas
    $user = \App\Models\User::factory()->create();

    $doc = Document::create([
        'name' => 'Manual de Políticas',
        'original_filename' => 'manual.pdf',
        'mime' => 'application/pdf',
        'size_bytes' => 111,
        'storage_path' => 'documents/manual.pdf',
        'page_count' => 2,
        'language' => 'es',
        'checksum_md5' => 'xyz',
        'user_id' => $user->id,
    ]);

    // Página “relevante”
    $page1 = DocumentPage::create([
        'document_id' => $doc->id,
        'page_number' => 1,
        'content' => 'La cláusula de terminación del contrato está en esta página',
        'embedding' => app(\App\Services\Embeddings\EmbeddingService::class)->embed(
            'La cláusula de terminación del contrato está en esta página'
        ),
    ]);

    // Página “menos relevante”
    $page2 = DocumentPage::create([
        'document_id' => $doc->id,
        'page_number' => 2,
        'content' => 'Contenido genérico sin relación',
        'embedding' => app(\App\Services\Embeddings\EmbeddingService::class)->embed(
            'Contenido genérico sin relación'
        ),
    ]);

    // Consulta
    $res = $this->postJson('/api/query', [
        'question' => '¿Dónde dice la cláusula de terminación?',
        'k' => 2,
    ]);

    $res->assertOk()->assertJsonStructure(['question','results'=>[['page_id','document_name','score']]]);
    $results = $res->json('results');

    expect($results)->toHaveCount(2);
    // La primera debería ser la página 1 por cercanía semántica
    expect($results[0]['page_number'])->toBe(1);
});
