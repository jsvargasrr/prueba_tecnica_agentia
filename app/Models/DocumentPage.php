<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Pgvector\Laravel\Casts\Vector;

class DocumentPage extends Model
{
    protected $fillable = ['document_id','page_number','content','embedding'];
    protected $casts = ['embedding' => Vector::class];
    public function document() { return $this->belongsTo(Document::class); }
}
