.PHONY: help install test test-browser test-feature test-unit test-all test-parallel test-coverage generate-test generate-suite report clean setup

# Colors
BLUE := \033[0;34m
GREEN := \033[0;32m
YELLOW := \033[1;33m
NC := \033[0m # No Color

help: ## Display this help message
	@echo "$(BLUE)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(NC)"
	@echo "$(GREEN)  E2E Test Automation System$(NC)"
	@echo "$(BLUE)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(NC)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(YELLOW)%-20s$(NC) %s\n", $$1, $$2}'
	@echo ""

setup: ## Setup E2E testing environment
	@echo "$(BLUE)Setting up E2E testing environment...$(NC)"
	@chmod +x scripts/*.sh
	@./scripts/install-dusk.sh
	@mkdir -p storage/test-reports
	@mkdir -p tests/Browser
	@echo "$(GREEN)✓ Setup complete!$(NC)"

install: ## Install dependencies
	@echo "$(BLUE)Installing dependencies...$(NC)"
	@composer install
	@echo "$(GREEN)✓ Dependencies installed!$(NC)"

test: ## Run all tests
	@echo "$(BLUE)Running all tests...$(NC)"
	@./scripts/run-e2e-tests.sh --all

test-browser: ## Run browser tests only
	@echo "$(BLUE)Running browser tests...$(NC)"
	@./scripts/run-e2e-tests.sh --browser

test-feature: ## Run feature tests only
	@echo "$(BLUE)Running feature tests...$(NC)"
	@./scripts/run-e2e-tests.sh --feature

test-unit: ## Run unit tests only
	@echo "$(BLUE)Running unit tests...$(NC)"
	@./scripts/run-e2e-tests.sh --unit

test-all: ## Run all tests with coverage
	@echo "$(BLUE)Running all tests with coverage...$(NC)"
	@./scripts/run-e2e-tests.sh --all --coverage

test-parallel: ## Run tests in parallel
	@echo "$(BLUE)Running tests in parallel...$(NC)"
	@./scripts/run-e2e-tests.sh --all --parallel

test-coverage: ## Run tests with coverage report
	@echo "$(BLUE)Running tests with coverage...$(NC)"
	@vendor/bin/phpunit --coverage-html storage/test-reports/coverage
	@echo "$(GREEN)✓ Coverage report: storage/test-reports/coverage/index.html$(NC)"

generate-test: ## Generate a single test (usage: make generate-test NAME=HomeTest URL=/ TYPE=browser)
	@echo "$(BLUE)Generating test...$(NC)"
	@php artisan test:generate-e2e $(NAME) --url=$(URL) --type=$(TYPE)
	@echo "$(GREEN)✓ Test generated!$(NC)"

generate-suite: ## Generate complete test suite
	@echo "$(BLUE)Generating test suite...$(NC)"
	@php artisan test:generate-suite --type=all
	@echo "$(GREEN)✓ Test suite generated!$(NC)"

report: ## Generate test report
	@echo "$(BLUE)Generating test report...$(NC)"
	@php artisan test:generate-report --format=html
	@php artisan test:generate-report --format=json
	@php artisan test:generate-report --format=markdown
	@echo "$(GREEN)✓ Reports generated!$(NC)"

report-html: ## Generate HTML test report
	@php artisan test:generate-report --format=html --output=storage/test-reports/latest.html
	@echo "$(GREEN)✓ HTML report: storage/test-reports/latest.html$(NC)"

report-json: ## Generate JSON test report
	@php artisan test:generate-report --format=json --output=storage/test-reports/latest.json
	@echo "$(GREEN)✓ JSON report: storage/test-reports/latest.json$(NC)"

report-markdown: ## Generate Markdown test report
	@php artisan test:generate-report --format=markdown --output=storage/test-reports/latest.md
	@echo "$(GREEN)✓ Markdown report: storage/test-reports/latest.md$(NC)"

view-report: ## Open latest test report in browser
	@echo "$(BLUE)Opening test report...$(NC)"
	@open storage/test-reports/latest.html || xdg-open storage/test-reports/latest.html || start storage/test-reports/latest.html

serve-reports: ## Start local server for test reports
	@echo "$(BLUE)Starting local server...$(NC)"
	@echo "$(GREEN)Open http://localhost:8080/latest.html in your browser$(NC)"
	@php -S localhost:8080 -t storage/test-reports

clean: ## Clean test artifacts
	@echo "$(BLUE)Cleaning test artifacts...$(NC)"
	@rm -rf storage/test-reports/*.html
	@rm -rf storage/test-reports/*.json
	@rm -rf storage/test-reports/*.xml
	@rm -rf storage/test-reports/coverage
	@rm -rf tests/Browser/screenshots/*.png
	@rm -rf tests/Browser/console/*.log
	@echo "$(GREEN)✓ Cleaned!$(NC)"

clean-all: clean ## Clean all test files and artifacts
	@echo "$(BLUE)Cleaning all test files...$(NC)"
	@rm -rf tests/Browser/*.php
	@rm -rf tests/Feature/Api/*.php
	@echo "$(GREEN)✓ All cleaned!$(NC)"

lint: ## Run code linter
	@echo "$(BLUE)Running linter...$(NC)"
	@./vendor/bin/pint
	@echo "$(GREEN)✓ Linting complete!$(NC)"

format: lint ## Format code

fix: lint ## Fix code style issues

dusk-install: ## Install/Update Chrome Driver
	@echo "$(BLUE)Installing Chrome Driver...$(NC)"
	@php artisan dusk:chrome-driver --detect
	@echo "$(GREEN)✓ Chrome Driver installed!$(NC)"

dusk-update: dusk-install ## Update Chrome Driver

ci: ## Run CI pipeline locally
	@echo "$(BLUE)Running CI pipeline...$(NC)"
	@make install
	@make test-all
	@make report
	@echo "$(GREEN)✓ CI pipeline complete!$(NC)"

quick: ## Quick test run (fastest)
	@echo "$(BLUE)Running quick tests...$(NC)"
	@vendor/bin/phpunit --testsuite Unit
	@echo "$(GREEN)✓ Quick tests complete!$(NC)"

watch: ## Watch for changes and run tests
	@echo "$(BLUE)Watching for changes...$(NC)"
	@while true; do \
		inotifywait -q -r -e modify app/ tests/; \
		make quick; \
	done

status: ## Show test status
	@echo "$(BLUE)Test Status:$(NC)"
	@echo ""
	@echo "$(YELLOW)Test Files:$(NC)"
	@find tests -name "*.php" -type f | wc -l | xargs echo "  Total:"
	@find tests/Browser -name "*.php" -type f | wc -l | xargs echo "  Browser:"
	@find tests/Feature -name "*.php" -type f | wc -l | xargs echo "  Feature:"
	@find tests/Unit -name "*.php" -type f | wc -l | xargs echo "  Unit:"
	@echo ""
	@echo "$(YELLOW)Reports:$(NC)"
	@ls -lh storage/test-reports/*.html 2>/dev/null | wc -l | xargs echo "  HTML Reports:" || echo "  HTML Reports: 0"
	@echo ""

info: ## Display system information
	@echo "$(BLUE)System Information:$(NC)"
	@echo ""
	@echo "$(YELLOW)PHP Version:$(NC)"
	@php --version | head -n 1
	@echo ""
	@echo "$(YELLOW)Composer Version:$(NC)"
	@composer --version
	@echo ""
	@echo "$(YELLOW)Laravel Version:$(NC)"
	@php artisan --version
	@echo ""
	@echo "$(YELLOW)Chrome Driver:$(NC)"
	@./vendor/laravel/dusk/bin/chromedriver-linux --version 2>/dev/null || echo "Not installed"
	@echo ""

demo: ## Run demo test suite
	@echo "$(BLUE)Running demo tests...$(NC)"
	@php artisan test:generate-e2e DemoTest --url=/ --type=browser
	@php artisan dusk tests/Browser/DemoTest.php
	@make view-report
	@echo "$(GREEN)✓ Demo complete!$(NC)"

# Default target
.DEFAULT_GOAL := help
