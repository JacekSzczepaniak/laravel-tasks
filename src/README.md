# Laravel Tasks

Projekt demonstracyjny (proof of concept) aplikacji opartej o **Laravel 12**, przygotowany w architekturze **heksagonalnej** (DDD-inspired).  
Zawiera moduł zarządzania zadaniami (CRUD, filtry, przypisywanie obserwatorów) z prostym frontendem opartym o **Livewire**.

---

## Spis treści

- Wymagania
- Szybki start (Docker)
- Konfiguracja (.env)
- Workflow developerski (Make)
- Architektura i założenia
- Stos technologiczny
- DevOps / CI/CD
- Testy
- Troubleshooting
- Roadmap

---

## 📦 Wymagania

- Docker + Docker Compose
- Make (zalecane do obsługi workflow)
- PHP 8.2+ (jeśli uruchamiane bez Dockera)
- Node.js 20+ (kompilacja assetów, jeśli potrzebna)
- Composer 2

---

## 🚀 Uruchomienie na nowym środowisku

1. Sklonuj repozytorium:
   ```bash
   git clone git@github.com:JacekSzczepaniak/laravel-tasks.git
   cd laravel-tasks
   ```
   
2. Uruchom środowisko Dockera:
    ```bash
    make up
    ```

3. Zainstaluj zależności PHP:

    ```bash
    make composer-install
    ```
4. Skonfiguruj plik `.env` (patrz sekcja Konfiguracja) i wygeneruj klucz:
   ```bash
   php artisan key:generate
   ```
5. Migracje i dane przykładowe:

    ```bash
    make migrate
    make seed
    ```
6. Aplikacja dostępna będzie pod adresem:

   - http://localhost:8080

---

## ⚙️ Konfiguracja (.env)

Skopiuj `.env.example` do `.env` i uzupełnij kluczowe ustawienia:

Najważniejsze zmienne:
- APP_ENV, APP_DEBUG, APP_URL (np. http://localhost:8080)
- DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
- CACHE_DRIVER
---

## 🧑‍💻 Workflow developerski (Make)

Najczęściej używane komendy:
- make up — uruchomienie środowiska
- make down — zatrzymanie środowiska
- make composer-install — instalacja zależności PHP
- make cs-fixer — formatowanie kodu (PHP CS Fixer)
- make phpstan — analiza statyczna kodu
- make pest — uruchomienie testów (PHPUnit / Pest)
- make swagger-generate — generowanie dokumentacji OpenAPI (l5-swagger)

---

## 🏗 Architektura i założenia

Architektura heksagonalna – warstwa domenowa oddzielona od frameworka

CQRS-lite – komendy/aplikacje obsługują logikę (np. CreateTask, UpdateTask)

Encje domenowe – reprezentują biznesowe TaskEntity

Adaptery infrastrukturalne – Eloquent jako repozytoria danych

Livewire – warstwa prezentacji (komponenty, paginacja, formularze)

REST API – dostęp do zasobów w warstwie kontrolerów, zgodny z OpenAPI

## 🔧 Stos technologiczny

- Backend: Laravel 12, Eloquent ORM
- Prezentacja: Livewire, Blade
- Frontend: Vite, TailwindCSS, Alpine.js
- Narzędzia jakości: PHP CS Fixer, PHPStan
- Testy: PHPUnit, Pest
- Dokumentacja API: l5-swagger (OpenAPI)

## ⚙️ DevOps / CI/CD

Docker Compose – uruchamianie środowiska developerskiego

Makefile – spójny workflow developerski

PHPUnit + Pest – testy jednostkowe i integracyjne

PHPStan – analiza statyczna

PHP CS Fixer – automatyczne formatowanie kodu

OpenAPI (l5-swagger) – automatyczna dokumentacja API

(opcjonalnie) integracja z GitHub Actions / pipeline CI (do dopisania)

---

## ✅ Testy

- Uruchomienie testów:
  ```bash
  make pest
  ```
  (Make uruchamia testy niezależnie od tego, czy scenariusze są w PHPUnit czy w Pest.)
- Analiza statyczna:
  ```bash
  make phpstan
  ```
- Formatowanie:
  ```bash
  make cs-check
  make cs-fixer
  ```

---

## 🛠 Troubleshooting

- Po zmianie `.env` zrestartuj kontenery lub wyczyść cache:
  ```bash
  make cache-clear
  make config-clear
  make route-clear
  make view-clear
  ```
- Port 8080 zajęty? Zmień publikowany port w `docker-compose.yml` i w `APP_URL`.
- Problemy z uprawnieniami storage/cache:
  ```bash
  php artisan storage:link
  chmod -R 777 storage bootstrap/cache
  ```

---

## 📖 Roadmap

- ✅ CRUD dla zadań
- ✅ Filtrowanie i paginacja
- ✅ Livewire + UI
- ✅ OpenAPI docs
- Uzupełnienie testów end-to-end
- Deployment (CI/CD pipeline)
- Rozszerzenie domeny o dodatkowe moduły
