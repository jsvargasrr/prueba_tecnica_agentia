<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'name',
        'original_filename',
        'mime',
        'size_bytes',
        'storage_path',
        'page_count',
        'language',
        'checksum_md5',
        'user_id'
    ];

    public function pages()
    {
        return $this->hasMany(DocumentPage::class);
    }
}
