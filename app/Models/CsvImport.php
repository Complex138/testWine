<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CsvImport extends Model
{
    protected $fillable = [
        'filename',
        'status',
        'total_rows',
        'processed_rows',
        'draft_rows',
        'errors',
        'warnings',
        'duplicates',
        'drafts'
    ];

    protected $casts = [
        'errors' => 'array',
        'warnings' => 'array',
        'duplicates' => 'array',
        'drafts' => 'array'
    ];
}
