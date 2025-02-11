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
php artisan migrate:fresh

php artisan db:seed

php artisan optimize:clear

# Запуск очереди
php artisan queue:work

# Сборка и запуск

Запуск билда vue

npm run dev # для разработки

npm run build # для прода


# Запуск http сервера

php artisan serve

или использовать http сервер