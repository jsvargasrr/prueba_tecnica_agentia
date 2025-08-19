<?php

namespace App\Http\Controllers;

use App\Models\DocumentPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Pgvector\Laravel\Vector;
use App\Services\Embeddings\EmbeddingService;

class QueryController extends Controller
{
    public function search(Request $req, EmbeddingService $embeddings)
    {
        $data = $req->validate([
            'question'     => 'required|string',
            'k'            => 'nullable|integer|min:1|max:20',
            'document_id'  => 'nullable|integer|exists:documents,id',
        ]);

        $k = $data['k'] ?? 5;
        $q = $data['question'];
        $vec = $embeddings->embed($q);                 // normalizado
        $vector = new Vector($vec);                    // casteo pgvector/laravel

        // Guardar historial de consulta (si hay user)
        $queryId = DB::table('queries')->insertGetId([
            'user_id' => optional($req->user())->id,
            'question' => $q,
            'top_k' => $k,
            'response_lang' => app()->getLocale(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Filtro opcional por documento
        $where = $data['document_id'] ? 'WHERE p.document_id = :docId' : '';

        // Consulta ordenando por distancia coseno (<=>). Menor = más similar.
        $sql = "
            SELECT p.id, p.page_number, p.content, d.name as document_name,
                   (p.embedding <=> :query_vec) AS distance
            FROM document_pages p
            JOIN documents d ON d.id = p.document_id
            $where
            ORDER BY p.embedding <=> :query_vec
            LIMIT :k
        ";

        // Bind especial para vector (pgvector/laravel se encarga si usas Vector)
        $rows = DB::select($sql, [
            'query_vec' => $vector,
            'docId'     => $data['document_id'] ?? null,
            'k'         => $k,
        ]);

        // Persistir top-k
        foreach ($rows as $rank => $r) {
            DB::table('query_results')->insert([
                'query_id' => $queryId,
                'document_page_id' => $r->id,
                'score' => $r->distance,
                'rank' => $rank + 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Respuesta
        return response()->json([
            'question' => $q,
            'results' => array_map(fn($r) => [
                'page_id'       => $r->id,
                'page_number'   => $r->page_number,
                'document_name' => $r->document_name,
                'score'         => $r->distance,
                // snippet corto y seguro
                'content_snippet'=> mb_strimwidth(preg_replace('/\s+/', ' ', $r->content ?? ''), 0, 400, '…'),
            ], $rows),
        ]);
    }
}
