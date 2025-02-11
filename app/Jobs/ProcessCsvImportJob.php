<?php

namespace App\Jobs;

use App\Models\CsvImport;
use App\Services\CsvImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessCsvImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $filePath,
        private int $importId
    ) {}

    public function handle(CsvImportService $service): void
    {
        try {
            Log::info('Starting CSV import job', [
                'file' => $this->filePath,
                'import_id' => $this->importId
            ]);

            // Проверяем существование файла перед обработкой
            if (!Storage::exists($this->filePath)) {
                throw new \Exception('Import file not found: ' . $this->filePath);
            }

            $service->processFile($this->filePath, $this->importId);
        } catch (\Exception $e) {
            Log::error('CSV Import Job Error', [
                'error' => $e->getMessage(),
                'file' => $this->filePath,
                'import_id' => $this->importId,
                'trace' => $e->getTraceAsString()
            ]);

            // Обновляем статус импорта
            CsvImport::where('id', $this->importId)->update([
                'status' => 'failed',
                'errors' => [$e->getMessage()]
            ]);
        }
    }
}
