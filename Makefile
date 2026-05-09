DOCKER_APP = docker-compose exec backend

.PHONY: help up down install db-init cache-clear bash test coverage assets-vendor assets-build assets-build-prod

help:
	@echo "Comandes disponibles:"
	@echo "  make up               - Aixeca tots els contenidors (Load Balancer, 3 Apps, BD, Redis)"
	@echo "  make down             - Atura i elimina els contenidors"
	@echo "  make install          - Executa 'composer install' i descarrega els assets de tercers"
	@echo "  make db-init          - Crea la base de dades i executa les migracions"
	@echo "  make cache-clear      - Neteja la memòria cau de Symfony i Redis, i reinicia per buidar APCu"
	@echo "  make bash             - Entra dins del primer contenidor de Symfony per executar coses manualment"
	@echo "  make assets-vendor    - Descarrega CSS de tercers (DaisyUI) a assets/vendor/"
	@echo "  make assets-build     - Compila Tailwind CSS en mode watch (dev)"
	@echo "  make assets-build-prod - Compila i minifica Tailwind CSS per a producció"

up:
	docker-compose up -d

down:
	docker-compose down

install:
	$(DOCKER_APP) git config --global --add safe.directory /var/www/html
	$(DOCKER_APP) composer install
	$(MAKE) assets-vendor

db-init:
	$(DOCKER_APP) php bin/console doctrine:database:create --if-not-exists
	$(DOCKER_APP) php bin/console doctrine:migrations:migrate --no-interaction
	$(DOCKER_APP) php bin/console doctrine:fixtures:load

cache-clear:
	$(DOCKER_APP) php bin/console cache:clear
	docker-compose restart backend

bash:
	$(DOCKER_APP) bash

test:
	$(DOCKER_APP) php bin/phpunit

coverage:
	$(DOCKER_APP) php -d pcov.enabled=1 bin/phpunit --coverage-html coverage/html --coverage-text

assets-vendor:
	$(DOCKER_APP) mkdir -p assets/vendor
	$(DOCKER_APP) curl -fsSL "https://cdn.jsdelivr.net/npm/daisyui@4/dist/full.min.css" -o assets/vendor/daisyui.min.css

assets-build:
	$(DOCKER_APP) php bin/console tailwind:build --watch

assets-build-prod:
	$(DOCKER_APP) php bin/console tailwind:build --minify
	$(DOCKER_APP) php bin/console asset-map:compile
