# ===== Konfiguracja =====
COMPOSE = docker compose
APP = $(COMPOSE) exec app
NODE = $(COMPOSE) exec node

# ===== Help =====
help: ## Wyświetl listę dostępnych komend
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| sort \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

# ===== Komendy główne =====
up: ## Uruchom kontenery w tle
	$(COMPOSE) up -d

down: ## Zatrzymaj i usuń kontenery
	$(COMPOSE) down

build: ## Przebuduj i uruchom kontenery
	$(COMPOSE) up -d --build

bash: ## Wejdź do kontenera aplikacji (bash)
	$(APP) bash

# ===== Jakość kodu =====
phpstan: ## Uruchom analizę PHPStan
	$(APP) vendor/bin/phpstan analyse

cs-fixer: ## Napraw kod PHP-CS-Fixerem
	$(APP) vendor/bin/php-cs-fixer fix --allow-risky=yes

cs-check: ## Sprawdź kod PHP-CS-Fixerem bez zmian
	$(APP) vendor/bin/php-cs-fixer fix --dry-run --diff

# ===== Migracje / seedy =====
migrate: ## Wykonaj migracje bazy
	$(APP) php artisan migrate

seed: ## Uruchom seedery bazy
	$(APP) php artisan db:seed

# ===== Vite / Node =====
npm-install: ## Zainstaluj paczki NPM w kontenerze node (npm ci)
	$(COMPOSE) run --rm node npm ci

vite-dev: ## Uruchom Vite dev server na porcie 5173 (bez Nginxa)
	$(COMPOSE) run --rm -p 5173:5173 node npm run dev -- --host

vite-build: ## Zbuduj assety (Vite build)
	$(COMPOSE) run --rm node npm run build

# ===== Artisan =====
artisan: ## Uruchom dowolną komendę Artisan, np. make artisan ARGS="cache:clear"
	$(APP) php artisan $(ARGS)

# ===== Cache management =====
cache-clear: ## Wyczyść cache aplikacji
	$(APP) php artisan cache:clear

config-clear: ## Wyczyść cache konfiguracji
	$(APP) php artisan config:clear

route-clear: ## Wyczyść cache tras
	$(APP) php artisan route:clear

view-clear: ## Wyczyść cache widoków
	$(APP) php artisan view:clear

# ===== Testy =====
phpunit: ## Uruchom testy PHPUnit, np. make phpunit ARGS="--filter MyTest"
	$(APP) vendor/bin/phpunit $(ARGS)

pest: ## Uruchom testy Pest, np. make pest ARGS="--filter MyTest"
	$(APP) vendor/bin/pest $(ARGS)

# ===== Artisan serve (bez Nginxa) =====
serve: ## Uruchom wbudowany serwer Laravel na :8000 (bez Nginxa)
	$(COMPOSE) run --rm -p 8000:8000 app php artisan serve --host=0.0.0.0 --port=8000

swagger-generate: ## Generuj OpenAPI (l5-swagger)
	$(APP) php artisan l5-swagger:generate

swagger-open: ## Podaj URL dokumentacji
	@echo "➡  http://localhost:8080/api/documentation"