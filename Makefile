DOCKER_APP = docker-compose exec backend

.PHONY: help up down install db-init cache-clear bash

help:
	@echo "Comandes disponibles:"
	@echo "  make up          - Aixeca tots els contenidors (Load Balancer, 3 Apps, BD, Redis)"
	@echo "  make down        - Atura i elimina els contenidors"
	@echo "  make install     - Executa 'composer install' dins d'una instància"
	@echo "  make db-init     - Crea la base de dades i executa les migracions"
	@echo "  make cache-clear - Neteja la memòria cau de Symfony i Redis, i reinicia per buidar APCu"
	@echo "  make bash        - Entra dins del primer contenidor de Symfony per executar coses manualment"

up:
	docker-compose up -d

down:
	docker-compose down

install:
	$(DOCKER_APP) git config --global --add safe.directory /var/www/html
	$(DOCKER_APP) composer install

db-init:
	$(DOCKER_APP) php bin/console doctrine:database:create --if-not-exists
	$(DOCKER_APP) php bin/console doctrine:migrations:migrate --no-interaction
	$(DOCKER_APP) php bin/console doctrine:fixtures:load

cache-clear:
	$(DOCKER_APP) php bin/console cache:clear
	docker-compose restart backend

bash:
	$(DOCKER_APP) bash
