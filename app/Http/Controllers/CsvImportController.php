<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCsvImportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\CsvImport;
use DateTime;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CsvImportController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:10240'
            ]
        ]);

        try {
            // Сохраняем в папку csv_imports внутри private директории
            $originalFilename = $request->file('file')->getClientOriginalName() . "_ID" . floor(microtime(true) * 1000);
            $path = $request->file('file')->storeAs('csv_imports', $originalFilename, 'local');

            Log::info('File uploaded', ['path' => $path, 'full_path' => Storage::disk('local')->path($path)]);

            // Проверяем существование файла
            if (!Storage::disk('local')->exists($path)) {
                throw new \Exception('Uploaded file not found in storage');
            }

            // Проверяем что это действительно CSV с правильным разделителем
            $content = Storage::disk('local')->get($path);
            $firstLine = strtok($content, "\n");

            if (substr_count($firstLine, ';') < 4) {
                Storage::disk('local')->delete($path);
                return back()->with('error', 'Неверный формат CSV файла. Используйте разделитель ";"');
            }

            // Создаем запись об импорте
            $import = CsvImport::create([
                'filename' => $originalFilename,
                'status' => 'pending',
                'total_rows' => 0,
                'processed_rows' => 0,
                'draft_rows' => 0
            ]);

            Log::info('Created import record', ['import_id' => $import->id]);

            // Отправляем в очередь на обработку
            ProcessCsvImportJob::dispatch($path, $import->id);

            return back()->with('message', 'Файл успешно загружен и отправлен на обработку');
        } catch (\Exception $e) {
            Log::error('CSV Upload Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Ошибка при загрузке файла: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $imports = CsvImport::latest()
            ->paginate(10);

        return Inertia::render('Imports/Index', [
            'imports' => $imports
        ]);
    }

    public function show(CsvImport $import)
    {
        return Inertia::render('Imports/Show', [
            'importData' => $import // Переименовали проп
        ]);
    }
}
