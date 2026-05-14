# Common dev tasks for battlesnake-platform.
# Run `make help` for a summary.

SHELL := /bin/bash

# Pass host UID/GID to compose so containers run as you (not root).
# This keeps any files created in the bind mount (var/snake.db, vendor/, etc.)
# owned by the host user, and avoids the FPM-worker permission denial.
# `env` is used so the leading UID= assignment works under bash, where UID is read-only.
UID := $(shell id -u)
GID := $(shell id -g)
COMPOSE := env UID=$(UID) GID=$(GID) docker compose
RUN := $(COMPOSE) run --rm php
EXEC := $(COMPOSE) exec php

.DEFAULT_GOAL := help

.PHONY: help
help: ## Show this help.
	@grep -E '^[a-zA-Z][a-zA-Z0-9_-]*:.*?## ' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-14s\033[0m %s\n", $$1, $$2}'

# ---- Stack lifecycle --------------------------------------------------------

.PHONY: build
build: ## Build (or rebuild) the dev image.
	$(COMPOSE) build

.PHONY: up
up: ## Start the stack in the background (PORT=N overrides default 8088).
	PORT=$(PORT) $(COMPOSE) up -d

.PHONY: down
down: ## Stop the stack and remove containers.
	$(COMPOSE) down

.PHONY: restart
restart: down up ## Restart the stack.

.PHONY: logs
logs: ## Tail logs from all services.
	$(COMPOSE) logs -f

.PHONY: ps
ps: ## Show service status.
	$(COMPOSE) ps

# ---- Inside-container shortcuts --------------------------------------------

.PHONY: shell
shell: ## Open a bash shell in the php container.
	$(RUN) bash

.PHONY: composer
composer: ## Pass arguments to composer (e.g. `make composer ARGS="require foo/bar"`).
	$(RUN) composer $(ARGS)

.PHONY: install
install: ## composer install
	$(RUN) composer install

# ---- App tasks (delegate to composer scripts in composer.json) -------------

.PHONY: migrate
migrate: ## Run database migrations.
	$(RUN) composer migrate

.PHONY: test
test: ## Run PHPUnit.
	$(RUN) composer test

.PHONY: behat
behat: ## Run Behat features.
	$(RUN) composer behat

.PHONY: stan
stan: ## Run PHPStan static analysis.
	$(RUN) composer stan

.PHONY: cs
cs: ## Run PHPCS code-style check.
	$(RUN) composer cs

.PHONY: check
check: test behat stan cs ## Run the full quality gate.

# ---- Optional profiles ------------------------------------------------------

.PHONY: mysql-up
mysql-up: ## Start the stack including the MySQL service (PORT=N supported).
	PORT=$(PORT) $(COMPOSE) --profile mysql up -d

.PHONY: play
play: ## Run a local Battlesnake game against the running snake (requires `make up`).
	$(COMPOSE) --profile play run --rm battlesnake \
		play --url http://caddy --name local

# ---- Maintenance ------------------------------------------------------------

.PHONY: clean
clean: ## Remove containers, named volumes, and the locally-built image.
	$(COMPOSE) down -v --rmi local

.PHONY: fresh
fresh: clean build up migrate ## Nuke everything and rebuild from zero, then migrate.
