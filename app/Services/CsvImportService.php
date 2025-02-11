<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wine;
use App\Models\CsvImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CsvImportService
{
    private CsvImport $importLog;
    private array $russianMonths = [
        'января' => '01',
        'февраля' => '02',
        'марта' => '03',
        'апреля' => '04',
        'мая' => '05',
        'июня' => '06',
        'июля' => '07',
        'августа' => '08',
        'сентября' => '09',
        'октября' => '10',
        'ноября' => '11',
        'декабря' => '12'
    ];

    private function normalizeAndCombineData(array $headers, array $row): array
    {
        // Убираем BOM-маркер из первого заголовка если он есть
        $headers[0] = preg_replace('/[\x{EF}\x{BB}\x{BF}]/u', '', $headers[0]);

        // Создаем ассоциативный массив
        $data = [];
        foreach ($headers as $index => $header) {
            $data[trim($header)] = $row[$index] ?? null;
        }

        return $data;
    }

    public function processFile(string $filePath): void
    {
        $drafts = []; // Инициализируем массив
        $uniqueDrafts = []; // Для уникальных черновиков

        $this->importLog = CsvImport::updateOrCreate(
            ['filename' => basename($filePath)], // Условие поиска
            [ // Данные для обновления (если запись найдена) или создания (если нет)
                'status' => 'processing',
                'total_rows' => 0,
                'processed_rows' => 0,
                'draft_rows' => 0,
                'errors' => [],
                'warnings' => [],
                'duplicates' => [],
                'drafts' => []
            ]
        );

        try {
            $content = Storage::disk('local')->get($filePath);
            $rows = explode("\n", str_replace("\r\n", "\n", $content));
            $headers = str_getcsv(array_shift($rows), ';');
            $rows = array_filter($rows);

            $this->importLog->update(['total_rows' => count($rows)]);

            foreach ($rows as $row) {
                $data = $this->normalizeAndCombineData($headers, str_getcsv($row, ';'));
                
                if (empty($data['ФИО']) || empty($data['Дата рождения'])) {
                    $draftKey = md5($data['Телефон'] . ($data['ФИО'] ?? '') . ($data['Дата рождения'] ?? ''));
                    if (!isset($uniqueDrafts[$draftKey])) {
                        $drafts[] = [
                            'name' => $data['ФИО'] ?? 'Без имени',
                            'phone' => $data['Телефон'] ?? 'нет',
                            'date' => $data['Дата рождения'] ?? 'не указана'
                        ];
                        $uniqueDrafts[$draftKey] = true;
                    }
                }
                
                $this->processRow($data);
            }

            $this->importLog->update([
                'status' => 'completed',
                'drafts' => $drafts,
                'draft_rows' => count($drafts)
            ]);

        } catch (\Exception $e) {
            Log::error('CSV Import Error', [
                'error' => $e->getMessage(),
                'file' => $filePath,
                'trace' => $e->getTraceAsString()
            ]);

            $this->importLog->update([
                'status' => 'failed',
                'errors' => array_merge($this->importLog->errors ?? [], [$e->getMessage()])
            ]);
        }
    }

    private function processRow(array $data): void
    {
        try {

            //var_dump($data); die();
            // Проверка на пустой телефон
            if (empty($data['Телефон'])) {
                $this->addWarning('Пропущена запись с пустым телефоном: ' . ($data['ФИО'] ?? 'Без имени'));
                return;
            }

            // Нормализуем телефон
            $phone = $this->normalizePhone($data['Телефон']);
            if (!$phone) {
                $this->addWarning('Некорректный формат телефона: ' . $data['Телефон']);
                return;
            }

            // Подготавливаем данные
            $birthDate = $this->normalizeDate($data['Дата рождения']);
            $isDraft = empty($data['ФИО']) || empty($birthDate);

            // Увеличиваем счетчики
            $isDraft ? $this->importLog->increment('draft_rows') : $this->importLog->increment('processed_rows');

            if ($isDraft) {
                // Это кусок оставлю, на случай если их надо заносить в БД пользователей. Дефолтный высер 1900 год
                empty($birthDate) && $birthDate = '1900-01-01';
                // Выходим из процедуры, чтобы не заносить пользователя в БД, только в счетчик черновика при импорте
                return;
            }

            // Обрабатываем вино
            $wineId = null;
            if (!empty($data['Любимое вино'])) {
                $wineName = $this->normalizeWineName($data['Любимое вино']);
                // тут вообщем функцию заюзаем чтобы не проверять на дубликаты, если есть пропускаем, если нету создаем.
                $wine = Wine::firstOrCreate(['name' => $wineName]);
                $wineId = $wine->id;
            }

            // Проверим на дубликаты
            if($this->checkDuplicates($data['ФИО'], $birthDate, $data['Адрес'])){
                return;
            }

            // Пытаемся создать пользователя через firstOrCreate
            $user = User::firstOrCreate(
                ['phone' => $phone],  // Условие поиска
                [                     // Данные для создания
                    'name' => $data['ФИО'] ?? '',
                    'birth_date' => $birthDate,
                    'address' => $data['Адрес'] ?? null,
                    'favorite_wine_id' => $wineId,
                    'is_draft' => $isDraft
                ]
            );

            // Если пользователь уже существовал
            if (!$user->wasRecentlyCreated) {
                $this->addWarning("Пользователь с телефоном {$phone} уже существует");
                return;
            }

            
        } catch (\Exception $e) {
            $this->addError('Ошибка обработки строки: ' . json_encode($data, JSON_UNESCAPED_UNICODE) . ' - ' . $e->getMessage());
        }
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (empty($phone)) return null;

        // Убираем все кроме цифр
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Проверяем длину
        if (strlen($phone) === 11) {
            // Если начинается с 7 или 8, заменяем на 7
            if ($phone[0] === '7' || $phone[0] === '8') {
                $phone = '7' . substr($phone, 1);
            } else {
                return null;
            }
        } elseif (strlen($phone) === 10) {
            // Добавляем 7 в начало
            $phone = '7' . $phone;
        } else {
            return null;
        }

        return '+' . $phone;
    }

    private function normalizeDate(?string $date): ?string
    {
        if (empty($date)) return null;

        try {
            // Формат DD.MM.YYYY
            if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date)) {
                return Carbon::createFromFormat('d.m.Y', $date)->format('Y-m-d');
            }

            // Формат "DD месяц YYYY г."
            if (preg_match('/(\d{1,2})\s+([а-яё]+)\s+(\d{4})(?:\s*г\.?)?/iu', $date, $matches)) {
                $month = mb_strtolower($matches[2], 'UTF-8');
                if (isset($this->russianMonths[$month])) {
                    return Carbon::createFromFormat(
                        'Y-m-d',
                        $matches[3] . '-' . $this->russianMonths[$month] . '-' . str_pad($matches[1], 2, '0', STR_PAD_LEFT)
                    )->format('Y-m-d');
                }
            }

            // Формат "YYYY, DD месяц"
            if (preg_match('/(\d{4}),\s*(\d{1,2})\s+([а-яё]+)/iu', $date, $matches)) {
                $month = mb_strtolower($matches[3], 'UTF-8');
                if (isset($this->russianMonths[$month])) {
                    return Carbon::createFromFormat(
                        'Y-m-d',
                        $matches[1] . '-' . $this->russianMonths[$month] . '-' . str_pad($matches[2], 2, '0', STR_PAD_LEFT)
                    )->format('Y-m-d');
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function normalizeWineName(string $name): string
    {
        $name = trim($name);
        $name = str_replace(['"Вино "', 'Вино "', '"', '"'], '', $name);
        return mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8');
    }

    private function checkDuplicates(string $name, string $birthDate, ?string $address): bool
    {
        $query = User::where('name', $name)->where('birth_date', $birthDate);

        // Полный дубликат
        if ($address && $query->where('address', $address)->exists()) {
            $this->addDuplicate("Найден полный дубликат: $name, $birthDate, $address");
            return true;
        }

        // Возможный дубликат
        if ($query->exists()) {
            $this->addWarning("Возможный дубликат: $name, $birthDate");
        }

        return false;
    }

    private function addError(string $message): void
    {
        $this->importLog->update([
            'errors' => array_merge($this->importLog->errors ?? [], [$message])
        ]);
    }

    private function addWarning(string $message): void
    {
        $this->importLog->update([
            'warnings' => array_merge($this->importLog->warnings ?? [], [$message])
        ]);
    }

    private function addDuplicate(string $message): void
    {
        $this->importLog->update([
            'duplicates' => array_merge($this->importLog->duplicates ?? [], [$message])
        ]);
    }
}
