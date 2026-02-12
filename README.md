```bash
docker compose up -d

docker compose exec backend php migrations/migrate.php

docker compose exec backend php seeds/seed.php
```

**Backend**: http://localhost:8080
**Frontend**: http://localhost:5173

## Тесты

```bash
docker compose exec backend vendor/bin/phpunit
```

## Подключение Telegram

1. Откройте @BotFather в Telegram
2. Отправьте /newbot и следуйте инструкциям
3. Скопируйте Bot Token
4. Перейдите в бот и нажмите /start

1. Откройте @Getmyid_Work_Bot и нажмите /start
2. Скопируйте ChatID

1. Откройте http://localhost:5173/shops/1/growth/telegram
2. Введите BotToken и ChatID
3. Нажмите Сохранить

## Допущения

- Реализовано на Slim4 + Vite. Можно было сделать на Laravel(Symfony)/next.js, но, кажется, для такой задачи они избыточны
- Аутентификация отсутствует
- Для создания заказа сделан upsert по номеру заказа
- PHP built-in сервер вместо Nginx
- Нет очередей
- Нет rate-limiting для Telegram API
- Для получения ChatID используется сторонний бот. Можно сделать свой.
- Запросы к БД через Doctrine DBAL. Миграции и сиды через простые SQL-скрипты (PDO)