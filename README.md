## Самый важный момент, проверить что файл CSV в кодеровке UTF-8 без BOOM! Важно, в PHP с ним целый гемор!

# После клонирования

composer install

npm install

# Настройка домена в .env

APP_URL=http://wine.test

VITE_SERVER_HOST=wine.test

# Настройка vite.config.js

server: {

    host: 'wine.test',

    hmr: { 
        
        host: 'wine.test' 
        
    }

}

# Настройка БД и очистка
php artisan migrate:fresh  Пересоздаем все миграции

php artisan db:seed  Это не обязательно просто мок данные

php artisan optimize:clear Сбросим кеш приложения на случай если что-то правиться.

# Запуск очереди
php artisan queue:work Запуск очередей важен для импорта CSV файлов

# Сборка и запуск

Запуск билда vue

npm run dev # для разработки

npm run build # для прода


# Запуск http сервера

php artisan serve

или использовать http сервер