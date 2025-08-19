<?php

namespace Tests\Fakes;

use App\Services\Embeddings\EmbeddingService;

class FakeEmbeddingService implements EmbeddingService
{
    public function dim(): int
    {
        return 8; // dimensión pequeña para tests
    }

    public function embed(string $text): array
    {
        // Vector determinista a partir de un hash
        $h = crc32($text);
        $v = [];
        for ($i = 0; $i < 8; $i++) {
            $v[$i] = (($h >> ($i * 4)) & 0xF) / 10.0; // 0.0..1.5
        }
        // normaliza L2
        $norm = sqrt(array_reduce($v, fn($c, $x) => $c + $x * $x, 0.0)) ?: 1.0;
        return array_map(fn($x) => $x / $norm, $v);
    }
}
