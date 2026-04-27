# Laravel Project Build System
# Usage: make [command]

.PHONY: help install dev build build-prod clean rebuild watch test lint format analyze serve fresh

# Default target
help:
	@echo "Available commands:"
	@echo "  make install     - Install all dependencies (npm + composer)"
	@echo "  make dev         - Start development server with hot reload"
	@echo "  make build       - Build assets for development"
	@echo "  make build-prod  - Build optimized assets for production"
	@echo "  make clean       - Remove build artifacts"
	@echo "  make rebuild     - Clean and rebuild"
	@echo "  make watch       - Watch for changes and rebuild"
	@echo "  make test        - Run tests (npm + phpunit)"
	@echo "  make lint        - Run linters"
	@echo "  make format      - Format code"
	@echo "  make analyze     - Build with bundle analysis"
	@echo "  make serve       - Start Laravel development server"
	@echo "  make fresh       - Fresh install (clean + install + build)"

# Install all dependencies
install:
	@echo "Installing npm dependencies..."
	npm install
	@echo "Installing composer dependencies..."
	composer install
	@echo "Dependencies installed successfully!"

# Development server with hot reload
dev:
	npm run dev

# Build for development
build:
	npm run build

# Build for production
build-prod:
	npm run build:prod

# Clean build artifacts
clean:
	npm run clean
	@echo "Build artifacts cleaned!"

# Clean and rebuild
rebuild:
	npm run rebuild

# Watch mode
watch:
	npm run watch

# Run tests
test:
	npm run test
	php artisan test

# Run tests in watch mode
test-watch:
	npm run test:watch

# Lint code
lint:
	npm run lint

# Lint and fix
lint-fix:
	npm run lint:fix

# Format code
format:
	npm run format

# TypeScript check
typecheck:
	npm run typecheck

# Bundle analysis
analyze:
	npm run analyze

# Start Laravel server
serve:
	php artisan serve

# Start both development servers
serve-all:
	npx concurrently "php artisan serve" "npm run dev"

# Fresh install
fresh: clean
	rm -rf node_modules vendor
	$(MAKE) install
	$(MAKE) build

# Database fresh migration
db-fresh:
	php artisan migrate:fresh --seed

# Cache clear
cache-clear:
	php artisan cache:clear
	php artisan config:clear
	php artisan route:clear
	php artisan view:clear

# Production optimization
optimize:
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
	npm run build:prod

# Deploy (production ready)
deploy: optimize
	@echo "Deployment ready!"
	@echo "Build completed with production optimizations."
