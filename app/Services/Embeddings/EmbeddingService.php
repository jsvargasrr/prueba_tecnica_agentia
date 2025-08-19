<?php

namespace App\Services\Embeddings;

interface EmbeddingService {
    /** @return float[] Embedding normalizado (L2 = 1.0) */
    public function embed(string $text): array;
    /** Dimensión esperada */
    public function dim(): int;
}
