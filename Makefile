# ===== Konfiguracja =====
COMPOSE = docker compose
APP_SERVICE ?= app
APP = $(COMPOSE) exec $(APP_SERVICE)
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
	$(COMPOSE) exec $(APP_SERVICE) vendor/bin/phpstan analyse || $(COMPOSE) run --rm $(APP_SERVICE) vendor/bin/phpstan analyse

cs-fixer: ## Napraw kod PHP-CS-Fixerem
	$(COMPOSE) exec $(APP_SERVICE) vendor/bin/php-cs-fixer fix --allow-risky=yes || $(COMPOSE) run --rm $(APP_SERVICE) vendor/bin/php-cs-fixer fix --allow-risky=yes

cs-check: ## Sprawdź kod PHP-CS-Fixerem bez zmian
	$(COMPOSE) exec $(APP_SERVICE) vendor/bin/php-cs-fixer fix --dry-run --diff || $(COMPOSE) run --rm $(APP_SERVICE) vendor/bin/php-cs-fixer fix --dry-run --diff

# ===== Migracje / seedy =====
migrate: ## Wykonaj migracje bazy
	$(COMPOSE) exec $(APP_SERVICE) php artisan migrate || $(COMPOSE) run --rm $(APP_SERVICE) php artisan migrate

seed: ## Uruchom seedery bazy
	$(COMPOSE) exec $(APP_SERVICE) php artisan db:seed || $(COMPOSE) run --rm $(APP_SERVICE) php artisan db:seed

# ===== Vite / Node =====
npm-install: ## Zainstaluj paczki NPM w kontenerze node (npm ci)
	$(COMPOSE) run --rm node npm ci

vite-dev: ## Uruchom Vite dev server na porcie 5173 (bez Nginxa)
	$(COMPOSE) run --rm -p 5173:5173 node npm run dev -- --host

vite-build: ## Zbuduj assety (Vite build)
	$(COMPOSE) run --rm node npm run build

# ===== Artisan =====
artisan: ## Uruchom dowolną komendę Artisan, np. make artisan ARGS="cache:clear"
	$(COMPOSE) exec $(APP_SERVICE) php artisan $(ARGS) || $(COMPOSE) run --rm $(APP_SERVICE) php artisan $(ARGS)

# ===== Cache management =====
cache-clear: ## Wyczyść cache aplikacji
	$(COMPOSE) exec $(APP_SERVICE) php artisan cache:clear || $(COMPOSE) run --rm $(APP_SERVICE) php artisan cache:clear

config-clear: ## Wyczyść cache konfiguracji
	$(COMPOSE) exec $(APP_SERVICE) php artisan config:clear || $(COMPOSE) run --rm $(APP_SERVICE) php artisan config:clear

route-clear: ## Wyczyść cache tras
	$(COMPOSE) exec $(APP_SERVICE) php artisan route:clear || $(COMPOSE) run --rm $(APP_SERVICE) php artisan route:clear

view-clear: ## Wyczyść cache widoków
	$(COMPOSE) exec $(APP_SERVICE) php artisan view:clear || $(COMPOSE) run --rm $(APP_SERVICE) php artisan view:clear

# ===== Testy =====
phpunit: ## Uruchom testy PHPUnit, np. make phpunit ARGS="--filter MyTest"
	$(COMPOSE) exec $(APP_SERVICE) vendor/bin/phpunit $(ARGS) || $(COMPOSE) run --rm $(APP_SERVICE) vendor/bin/phpunit $(ARGS)

pest: ## Uruchom testy Pest, np. make pest ARGS="--filter MyTest"
	$(COMPOSE) exec $(APP_SERVICE) vendor/bin/pest $(ARGS) || $(COMPOSE) run --rm $(APP_SERVICE) vendor/bin/pest $(ARGS)

# ===== Artisan serve (bez Nginxa) =====
serve: ## Uruchom wbudowany serwer Laravel na :8000 (bez Nginxa)
	$(COMPOSE) run --rm -p 8000:8000 $(APP_SERVICE) php artisan serve --host=0.0.0.0 --port=8000

swagger-generate: ## Generuj OpenAPI (l5-swagger)
	$(COMPOSE) exec $(APP_SERVICE) php artisan l5-swagger:generate || $(COMPOSE) run --rm $(APP_SERVICE) php artisan l5-swagger:generate

swagger-open: ## Podaj URL dokumentacji
	@echo "➡  http://localhost:8080/api/documentation"