<?php

use App\Jobs\BuildPageEmbedding;
use App\Services\PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('uploads a pdf, creates pages and dispatches embedding jobs', function () {
    Queue::fake();
    Storage::fake('local');

    $user = \App\Models\User::factory()->create(['password' => bcrypt('secret')]);
    $token = auth()->login($user);

    // Mock del servicio de PDF -> simula 2 páginas
    $this->mock(PdfService::class, function ($mock) {
        $mock->shouldReceive('pages')->once()->andReturn([
            "Primera página de contrato...",
            "Segunda página con anexo...",
        ]);
    });

    $fakePdf = UploadedFile::fake()->create('demo.pdf', 10, 'application/pdf');

    $res = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/documents/upload', [
            'file' => $fakePdf,
            'name' => 'Contrato Demo',
        ]);

    $res->assertCreated()->assertJson(['status' => 'ok']);

    // Se crean documento y páginas
    $doc = \App\Models\Document::first();
    expect($doc)->not->toBeNull();
    expect($doc->page_count)->toBe(2);

    $pages = \App\Models\DocumentPage::where('document_id', $doc->id)->get();
    expect($pages)->toHaveCount(2);

    // Se encolaron jobs de embeddings 
    Queue::assertPushed(BuildPageEmbedding::class, 2);
});
