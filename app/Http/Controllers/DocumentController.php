<?php

namespace App\Http\Controllers;

use App\Jobs\BuildPageEmbedding;
use App\Models\Document;
use App\Models\DocumentPage;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/documents/upload",
     *   tags={"Documents"},
     *   summary="Subir PDF y encolar embeddings",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(ref="#/components/parameters/AcceptLanguage"),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         required={"file"},
     *         @OA\Property(property="file", type="string", format="binary"),
     *         @OA\Property(property="name", type="string", example="Contrato 2025")
     *       )
     *     )
     *   ),
     *   @OA\Response(response=201, description="Creado",
     *     @OA\JsonContent(@OA\Property(property="status", type="string", example="ok"))
     *   ),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=422, description="Validación")
     * )
     */

    public function upload(Request $req, PdfService $pdf)
    {
        $req->validate([
            'file' => 'required|file|mimetypes:application/pdf|max:20480', // 20MB
            'name' => 'nullable|string|max:200',
        ]);

        $user = $req->user();
        $file = $req->file('file');

        // Guardar archivo
        $path = $file->store('documents'); // storage/app/documents/...
        $name = $req->input('name') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $pages = (new PdfService())->pages(Storage::path($path));

        DB::transaction(function () use ($user, $file, $path, $name, $pages) {
            $doc = Document::create([
                'name' => $name,
                'original_filename' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
                'storage_path' => $path,
                'page_count' => count($pages),
                'language' => app()->getLocale(),
                'checksum_md5' => md5_file($file->getRealPath()),
                'user_id' => $user->id,
            ]);

            foreach ($pages as $i => $content) {
                $page = DocumentPage::create([
                    'document_id' => $doc->id,
                    'page_number' => $i + 1,
                    'content' => $content,
                    'embedding' => array_fill(0, config('embeddings.dim'), 0.0), // placeholder
                ]);
                // Encolar job de embeddings
                BuildPageEmbedding::dispatch($page->id);
            }
        });

        return response()->json(['status' => 'ok'], 201);
    }

    /**
     * @OA\Post(
     *   path="/api/documents/upload-url",
     *   tags={"Documents"},
     *   summary="Subir PDF por URL (asincrónico)",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"url"},
     *       @OA\Property(property="url", type="string", example="https://example.com/file.pdf"),
     *       @OA\Property(property="name", type="string", nullable=true)
     *     )
     *   ),
     *   @OA\Response(response=202, description="Encolado",
     *     @OA\JsonContent(@OA\Property(property="queued", type="boolean", example=true))
     *   )
     * )
     */
    public function uploadUrl(Request $req)
    {
        $req->validate([
            'url'  => 'required|url',
            'name' => 'nullable|string|max:200',
        ]);

        App\Jobs\DownloadAndIngestDocument::dispatch(
            $req->user()->id,
            $req->input('url'),
            $req->input('name')
        );

        return response()->json(['queued' => true], 202);
    }
}
