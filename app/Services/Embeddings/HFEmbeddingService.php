<?php

namespace App\Services\Embeddings;

use GuzzleHttp\Client;

class HFEmbeddingService implements EmbeddingService
{
    private Client $http;
    private string $model;
    private int $dim;

    public function __construct()
    {
        $this->http  = new Client(['base_uri' => 'https://api-inference.huggingface.co/']);
        $this->model = env('HF_EMBED_MODEL', 'intfloat/multilingual-e5-small'); // ~384 dim
        $this->dim   = (int) env('EMBED_DIM', 384);
    }

    public function dim(): int { return $this->dim; }

    public function embed(string $text): array
    {
        // E5: recomienda prefijo "query: " / "passage: "
        $payload = ['inputs' => "passage: ".$text];
        $resp = $this->http->post("models/{$this->model}", [
            'headers' => ['Authorization' => 'Bearer '.env('HF_API_KEY')],
            'json'    => $payload,
            'timeout' => 30,
        ]);
        $data = json_decode((string)$resp->getBody(), true);
        $vec = is_array($data) && isset($data[0]) && is_array($data[0]) ? $data[0] : $data;
        // Normalizar L2
        $norm = sqrt(array_reduce($vec, fn($c,$x)=>$c + $x*$x, 0.0)) ?: 1.0;
        return array_map(fn($x)=>$x/$norm, $vec);
    }
}
