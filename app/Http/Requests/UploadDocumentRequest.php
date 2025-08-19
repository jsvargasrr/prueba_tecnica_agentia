<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => [
                'required','file','max:20480', // 20MB
                // Acepta PDFs genuinos
                'mimetypes:application/pdf,application/x-pdf,application/acrobat,applications/vnd.pdf'
            ],
            'name' => 'nullable|string|max:200',
        ];
    }
}
