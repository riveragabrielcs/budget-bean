# Laravel Sail Development Makefile
#
# This Makefile provides convenient shortcuts for common Laravel Sail operations.
# Run 'make help' to see all available commands.

# Variables
SAIL := ./vendor/bin/sail
COMPOSER := $(SAIL) composer
ARTISAN := $(SAIL) artisan
NPM := $(SAIL) npm

# Default target
.DEFAULT_GOAL := help

# Phony targets (targets that don't create files)
.PHONY: help up down restart build status logs shell tinker install update test test-coverage migrate seed fresh cache-clear route-list queue-work queue-restart npm-install npm-dev npm-build npm-watch clean reset

# Help - Display available commands
help: ## Show this help message
	@echo "Laravel Sail Development Commands"
	@echo "================================="
	@awk 'BEGIN {FS = ":.*##"} /^[a-zA-Z_-]+:.*##/ { printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

# Docker/Sail Commands
up: ## Start all containers in detached mode
	$(SAIL) up -d

down: ## Stop and remove all containers
	$(SAIL) down

restart: ## Restart all containers
	$(SAIL) restart

build: ## Build/rebuild all containers
	$(SAIL) build --no-cache

status: ## Show container status
	$(SAIL) ps

logs: ## Show container logs (use CONTAINER=service_name for specific service)
	$(SAIL) logs -f $(CONTAINER)

# Application Access
shell: ## Access the application container shell
	$(SAIL) shell

tinker: ## Start Laravel Tinker REPL
	$(ARTISAN) tinker

# Dependency Management
install: ## Install PHP and Node dependencies
	$(COMPOSER) install
	$(NPM) install

update: ## Update PHP and Node dependencies
	$(COMPOSER) update
	$(NPM) update

# Testing
test: ## Run PHPUnit tests
	$(SAIL) test

test-coverage: ## Run tests with coverage report
	$(SAIL) test --coverage

test-filter: ## Run specific test (use FILTER=TestName)
	$(SAIL) test --filter=$(FILTER)

# Database Commands
migrate: ## Run database migrations
	$(ARTISAN) migrate

migrate-fresh: ## Drop all tables and re-run migrations
	$(ARTISAN) migrate:fresh

seed: ## Run database seeders
	$(ARTISAN) db:seed

fresh: ## Fresh migration with seeding
	$(ARTISAN) migrate:fresh --seed

# Cache & Optimization
cache-clear: ## Clear all caches
	$(ARTISAN) cache:clear
	$(ARTISAN) config:clear
	$(ARTISAN) route:clear
	$(ARTISAN) view:clear

optimize: ## Optimize the application for production
	$(ARTISAN) config:cache
	$(ARTISAN) route:cache
	$(ARTISAN) view:cache

# Development Utilities
route-list: ## Display all registered routes
	$(ARTISAN) route:list

queue-work: ## Start processing queue jobs
	$(ARTISAN) queue:work

queue-restart: ## Restart queue workers
	$(ARTISAN) queue:restart

# Frontend Commands
npm-install: ## Install Node.js dependencies
	$(NPM) install

npm-dev: ## Run development build
	$(NPM) run dev

npm-build: ## Run production build
	$(NPM) run build

npm-watch: ## Run development build in watch mode
	$(NPM) run dev -- --watch

# Cleanup Commands
clean: ## Clean up temporary files and caches
	$(ARTISAN) cache:clear
	$(ARTISAN) config:clear
	$(ARTISAN) route:clear
	$(ARTISAN) view:clear
	$(COMPOSER) dump-autoload
	$(NPM) run build

reset: ## Complete reset - fresh install with dependencies
	$(SAIL) down
	$(COMPOSER) install
	$(NPM) install
	$(SAIL) up -d
	$(ARTISAN) migrate:fresh --seed
	$(NPM) run dev

# Custom Artisan Commands
artisan: ## Run custom artisan command (use CMD="your:command")
	$(ARTISAN) $(CMD)

# Database Backup (requires mysqldump in container)
backup-db: ## Create database backup
	$(SAIL) exec mysql mysqldump -u root -p$$MYSQL_ROOT_PASSWORD $$MYSQL_DATABASE > backup_$$(date +%Y%m%d_%H%M%S).sql

# Production Deployment Prep
deploy-prep: ## Prepare application for deployment
	$(COMPOSER) install --no-dev --optimize-autoloader
	$(NPM) run build
	$(ARTISAN) config:cache
	$(ARTISAN) route:cache
	$(ARTISAN) view:cache
