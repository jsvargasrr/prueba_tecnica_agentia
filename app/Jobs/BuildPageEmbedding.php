<?php

namespace App\Jobs;

use App\Models\DocumentPage;
use App\Services\Embeddings\EmbeddingService;

class BuildPageEmbedding extends \Illuminate\Bus\Queueable implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use \Illuminate\Foundation\Bus\Dispatchable, \Illuminate\Queue\InteractsWithQueue, \Illuminate\Queue\SerializesModels;

    public function __construct(public int $pageId) {}

    public function handle(EmbeddingService $embeddings): void
    {
        $page = DocumentPage::find($this->pageId);
        if (!$page || empty($page->content)) return;

        $vec = $embeddings->embed($page->content);
        // Validar dimensión
        if (count($vec) !== $embeddings->dim()) return;

        $page->embedding = $vec;
        $page->save();
    }
}
