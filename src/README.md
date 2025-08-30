# API w Laravel 12 z Dockerem

Projekt z Dockerem, Sanctum, modułem People + Contacts (1:N), wysyłką maili (Mailpit) oraz dokumentacją Swagger (l5-swagger).

## Stos

- PHP 8.x / Laravel 12
- MySQL 8 (container: `mysql-api`)
- Redis 7 (container: `redis-api`)
- Mailpit (podgląd e-maili)
- Laravel Sanctum (Bearer token)
- l5-swagger (OpenAPI)
- Docker Compose

## Wymagania

- Docker + Docker Compose
- (opcjonalnie) `make` — jeśli chcesz używać gotowych celów

## Szybki start

### 0) Klon i instalacja zależności

```
git clone <URL_REPO> laravel-api
cd laravel-api
```

### 1) Plik środowiskowy
  ``` cp .env.example .env```


Najważniejsze wartości dla dockera (sugerowane):
```
APP_NAME=Laravel API
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

# DB (kontener mysql-api)
DB_CONNECTION=mysql
DB_HOST=mysql-api
DB_PORT=3306
DB_DATABASE=laravel_api
DB_USERNAME=laravel
DB_PASSWORD=laravel

# Redis (kontener redis-api)
REDIS_HOST=redis-api
REDIS_PORT=6379

# Mailpit
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS=noreply@example.test
MAIL_FROM_NAME="${APP_NAME}"

# Kolejka (na start możesz zostawić sync)
QUEUE_CONNECTION=sync
```

### 2) Uruchom kontenery

a) Makefile

```make up```

b) Czysty docker compose

```docker compose up -d --build```

### 3) Composer + key
```   
docker compose exec app-api composer install
docker compose exec app-api php artisan key:generate
```

### 4) Migracje + seedy (admin + przykładowe osoby/kontakty)
   ```docker compose exec app-api php artisan migrate:fresh --seed```

### 5) Dokumentacja API (Swagger)

Wygeneruj:

```
docker compose exec app-api php artisan l5-swagger:generate
```

Otwórz UI:
http://localhost:8080/docs
(JSON: http://localhost:8080/api/documentation)

### 6) Testy

```make test```


### 7) Podgląd e-maili

Mailpit UI: http://localhost:8025
SMTP dla aplikacji: host mailpit, port 1025.

Uwierzytelnianie (Sanctum)

Rejestracja
```
curl -X POST http://localhost:8080/api/v1/auth/register \
-H "Content-Type: application/json" \
-d '{"name":"Alice","email":"alice@example.com","password":"password"}'
```

Logowanie (token)

```
curl -X POST http://localhost:8080/api/v1/auth/login \
-H "Content-Type: application/json" \
-d '{"email":"alice@example.com","password":"password"}'
# => {"token":"<BEARER>"}
```

Autoryzacja

```
curl -H "Authorization: Bearer <BEARER>" http://localhost:8080/api/v1/auth/me
```
Wylogowanie (unieważnia token)

```
curl -X POST -H "Authorization: Bearer <BEARER>" \
http://localhost:8080/api/v1/auth/logout
```

Harmonogram:

Upewnij się, że scheduler używa tej samej budowanej aplikacji co app-api.
Jeśli wcześniej miałeś błąd “pull access denied for app-api”, albo ustaw zmienną APP_IMAGE przy buildzie, albo zmień definicję scheduler, by korzystała z build context tak jak app-api.

Alternatywnie uruchamiaj ręcznie:

```docker compose exec app-api php artisan schedule:work```

Dev narzędzia

Wyczyszczanie cache’ów:

```docker compose exec app-api php artisan optimize:clear```


Przebudowa autoloadera:

```docker compose exec app-api composer dump-autoload```


Logi:

```docker compose logs -f app-api```

