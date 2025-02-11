# CRUD система управления пользователями и винами

Laravel 10 + Vue проект для управления пользователями и их любимыми винами с функцией импорта из CSV.

## Функционал

- CRUD операции с пользователями
- Управление списком вин
- Импорт из CSV с валидацией
- Система черновиков для неполных записей
- Проверка дубликатов
- Валидация телефонных номеров
- Очереди для обработки файлов

## Требования

- PHP >= 8.1
- Composer
- Node.js и npm
- MySQL/MariaDB
- Веб-сервер (Apache/Nginx)

## Установка

### 1. Клонирование репозитория

```bash
git clone https://github.com/Complex138/testWine.git
cd testWine
```

### 2. Установка зависимостей

```bash
composer install
npm install
```

### 3. Настройка окружения
# Можно пропустить .env файл присутствует
```bash
cp .env.example .env
php artisan key:generate
```

Настройте файл `.env`:

```env
APP_URL=http://wine.test
VITE_SERVER_HOST=wine.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wine
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
```

### 4. Настройка Vite

Обновите `vite.config.js`:

```javascript
export default defineConfig({
    // ...
    server: {
        https: false,
        host: 'wine.test',
        hmr: {
            host: 'wine.test'
        }
    },
    // ...
});
```

### 5. Настройка базы данных

```bash
php artisan migrate:fresh    # Создание таблиц БД
php artisan db:seed         # Заполнение начальными данными
php artisan optimize:clear  # Очистка кэша приложения
```

### 6. Запуск обработчика очередей

Для обработки CSV импорта:

```bash
php artisan queue:work
```

### 7. Сборка фронтенда

Для продакшена:
```bash
npm run build
```

Для разработки:
```bash
npm run dev
```

### 8. Настройка сервера

#### Использование локального сервера
```bash
php artisan serve
```

#### Использование виртуального хоста

Добавьте в файл hosts:
```
127.0.0.1 wine.test
```

Конфигурация Apache:
```apache
<VirtualHost *:80>
    ServerName wine.test
    DocumentRoot "/путь/к/проекту/public"
    <Directory "/путь/к/проекту/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Структура проекта

```
├── app
│   ├── Http
│   │   ├── Controllers
│   │   │   ├── UserController.php      # Контроллер пользователей
│   │   │   └── CsvImportController.php # Контроллер импорта
│   │   └── Requests
│   │       └── UserRequest.php         # Валидация форм
│   ├── Models
│   │   ├── User.php                    # Модель пользователя
│   │   ├── Wine.php                    # Модель вина
│   │   └── CsvImport.php              # Модель импорта
│   └── Services
│       └── CsvImportService.php        # Сервис импорта CSV
├── database
│   ├── migrations                      # Миграции БД
│   └── seeders                         # Сиды для тестовых данных
└── resources
    └── js
        └── Pages
            └── Users                    # Vue компоненты
                ├── Index.vue           # Список пользователей
                ├── Create.vue          # Создание пользователя
                └── Edit.vue            # Редактирование пользователя
```

## Использование

1. Откройте `http://wine.test` в браузере
2. Доступный функционал:
   - Просмотр/Создание/Редактирование/Удаление пользователей
   - Импорт пользователей из CSV
   - Просмотр результатов импорта и черновиков
   - Управление списком вин

## Важные замечания

- Убедитесь, что обработчик очередей запущен для импорта CSV
- Настройте права доступа для директорий storage и bootstrap/cache
- Для больших CSV файлов настройте лимиты памяти и времени выполнения в php.ini
- Настройте MySQL для работы с большими наборами данных

## Формат CSV файла

Файл должен содержать следующие колонки:
- ФИО
- Телефон (формат: +7XXXXXXXXXX)
- Адрес
- Дата рождения (форматы: DD.MM.YYYY или "DD месяц YYYY г.")
- Любимое вино

Пример:
```csv
ФИО;Телефон;Адрес;Дата рождения;Любимое вино
Иван Иванов;+7 999 123 45 67;Москва, ул. Пушкина, д. 1;02.09.1985;Château Margaux
```

## Обработка данных

При импорте:
1. Проверяется уникальность телефона
2. Нормализуются даты и телефоны
3. Создаются черновики для неполных записей
4. Проверяются дубликаты по имени/дате/адресу
5. Нормализуются названия вин