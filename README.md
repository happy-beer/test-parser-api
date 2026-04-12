# Test Parser API

Сервис на Laravel для выгрузки данных из внешнего API с пагинацией и сохранения в MySQL.

## Что делает проект

- Загружает сущности из внешнего API:
  - `orders`
  - `sales`
  - `incomes`
  - `stocks`
- Поддерживает постраничную выгрузку (`page`, `limit`)
- Сохраняет данные через `updateOrInsert` (idempotent-подход)
- Поддерживает ретраи для `429` и `5xx` с backoff
- Делает паузу между страницами (настраивается)

## Технологии

- PHP `^8.2`
- Laravel `^12`
- MySQL

## Быстрый старт

1. Установить зависимости:

```bash
composer install
```

2. Подготовить окружение:

```bash
cp .env.example .env
```

3. Настроить БД и API в `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=test_parser_api
DB_USERNAME=root
DB_PASSWORD=

WB_API_BASE_URL=
WB_API_KEY=
WB_API_EARLIEST_DATE=2010-01-01
WB_API_DEFAULT_LIMIT=100
WB_API_TIMEOUT_SECONDS=30
WB_API_PAGE_SLEEP_SECONDS=1
WB_API_RETRY_ATTEMPTS=3
WB_API_RETRY_BASE_MS=1000
```

4. Применить миграции:

```bash
php artisan migrate
```

## Команды синхронизации

### По отдельной сущности

```bash
php artisan sync:orders {dateFrom} {dateTo} [--limit=100] [--page=1]
php artisan sync:sales {dateFrom} {dateTo} [--limit=100] [--page=1]
php artisan sync:incomes {dateFrom} {dateTo} [--limit=100] [--page=1]
php artisan sync:stocks {dateFrom} [--limit=100] [--page=1]
```

Примечания:
- `limit` валидируется в диапазоне `1..500`
- если обязательные даты не переданы, команда запросит их интерактивно
- для `stocks` используется формат `Y-m-d` и ограничение по дате: `after:yesterday|before:tomorrow`

### Цикл по нескольким сущностям

```bash
php artisan sync:cycle [entities...] [--from=Y-m-d] [--to=Y-m-d] [--limit=100] [--test=0]
```

Примеры:

```bash
# Все сущности, полный прогон
php artisan sync:cycle

# Только orders и sales за диапазон
php artisan sync:cycle orders sales --from=2026-01-01 --to=2026-03-31

# Тестовый режим: до 10 страниц на сущность
php artisan sync:cycle --test=1
```

Примечания:
- если `entities` не указаны, запускаются все (`orders`, `sales`, `incomes`, `stocks`)
- если `--from/--to` не указаны:
  - `from` берётся из `WB_API_EARLIEST_DATE`
  - `to` берётся как текущая дата
- для `stocks` в цикле используется дата `today` (формат `Y-m-d`)

## Используемые таблицы (сущности)

Основные таблицы данных:

- `orders`
- `sales`
- `incomes`
- `stocks`

## Архитектура (кратко)

- `app/Services/EntitySyncService.php` — общий HTTP sync-сервис (пагинация, retry, сохранение)
- `app/Console/Commands/Sync/*` — команды синхронизации
- `app/Models/*` — Eloquent-модели сущностей
- `database/migrations/*` — структура БД

## Важные детали

- Внешний API ожидает токен в query-параметре `key`
- Формат ответа предполагается как JSON с массивом данных в поле `data`
- Максимальный размер страницы API: `500`

## Доступ к БД

https://vh434.timeweb.ru/pma/?&db=ch898762_testparserapi
- login ch898762_testparserapi
- pass 3APPd1tj
