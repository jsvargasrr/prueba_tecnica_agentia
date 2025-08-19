<?php

namespace App\Services\Embeddings;

use OpenAI;

class OpenAIEmbeddingService implements EmbeddingService
{
    private int $dim;
    private $client;
    private string $model;

    public function __construct()
    {
        $this->client = OpenAI::client(env('OPENAI_API_KEY'));
        $this->model = env('OPENAI_EMBED_MODEL', 'text-embedding-3-small'); // 1536
        $this->dim = (int) env('EMBED_DIM', 1536);
    }

    public function dim(): int { return $this->dim; }

    public function embed(string $text): array
    {
        $res = $this->client->embeddings()->create([
            'model' => $this->model,
            'input' => $text,
        ]);
        $vec = $res->data[0]->embedding;
        return $this->normalize($vec);
    }

    private function normalize(array $v): array
    {
        $norm = sqrt(array_reduce($v, fn($c,$x)=>$c + $x*$x, 0.0)) ?: 1.0;
        return array_map(fn($x)=>$x/$norm, $v);
    }
}
