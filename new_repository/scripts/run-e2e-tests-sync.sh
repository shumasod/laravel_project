#!/bin/bash
#
# E2E Test Execution Script with Enhanced Synchronization
#

set -e

cd "$(dirname "$0")/.."

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Configuration
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
REPORT_DIR="storage/test-reports"
HTML_REPORT="${REPORT_DIR}/test-report-${TIMESTAMP}.html"
JSON_REPORT="${REPORT_DIR}/test-report-${TIMESTAMP}.json"
LATEST_REPORT="${REPORT_DIR}/latest.html"
LOCK_DIR="storage/locks"
LOCK_FILE="${LOCK_DIR}/test-execution.lock"
PID_FILE="${LOCK_DIR}/test-execution.pid"

echo -e "${BLUE}============================================${NC}"
echo -e "${BLUE}  E2E Test Suite Execution (Synchronized)${NC}"
echo -e "${BLUE}============================================${NC}"
echo ""

# Create necessary directories
mkdir -p "${REPORT_DIR}"
mkdir -p "${LOCK_DIR}"

# Function to display help
show_help() {
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  --browser        Run browser tests only"
    echo "  --feature        Run feature tests only"
    echo "  --unit           Run unit tests only"
    echo "  --all            Run all tests (default)"
    echo "  --coverage       Generate code coverage report"
    echo "  --parallel       Run tests in parallel"
    echo "  --sync           Force synchronous execution"
    echo "  --wait           Wait for other test runs to complete"
    echo "  --timeout SEC    Set lock timeout in seconds (default: 300)"
    echo "  --help           Show this help message"
    echo ""
}

# Function to acquire lock
acquire_lock() {
    local timeout=${1:-300}
    local start_time=$(date +%s)
    local wait_time=0

    while [ -f "$LOCK_FILE" ]; do
        wait_time=$(($(date +%s) - start_time))

        if [ $wait_time -ge $timeout ]; then
            echo -e "${RED}✗ Timeout waiting for lock (${timeout}s)${NC}"

            # Check if process is still running
            if [ -f "$PID_FILE" ]; then
                local old_pid=$(cat "$PID_FILE")
                if ! ps -p "$old_pid" > /dev/null 2>&1; then
                    echo -e "${YELLOW}⚠ Removing stale lock${NC}"
                    rm -f "$LOCK_FILE" "$PID_FILE"
                    continue
                fi
            fi

            return 1
        fi

        echo -e "${YELLOW}⏳ Waiting for other test execution to complete... (${wait_time}s/${timeout}s)${NC}"
        sleep 5
    done

    # Create lock file
    echo "$$" > "$PID_FILE"
    touch "$LOCK_FILE"

    echo -e "${GREEN}✓ Lock acquired${NC}"
    return 0
}

# Function to release lock
release_lock() {
    rm -f "$LOCK_FILE" "$PID_FILE"
    echo -e "${GREEN}✓ Lock released${NC}"
}

# Trap to ensure lock is released on exit
trap release_lock EXIT INT TERM

# Parse arguments
TEST_SUITE="all"
COVERAGE=""
PARALLEL=""
FORCE_SYNC=""
SHOULD_WAIT=""
LOCK_TIMEOUT=300

while [[ $# -gt 0 ]]; do
    case $1 in
        --browser)
            TEST_SUITE="browser"
            shift
            ;;
        --feature)
            TEST_SUITE="feature"
            shift
            ;;
        --unit)
            TEST_SUITE="unit"
            shift
            ;;
        --all)
            TEST_SUITE="all"
            shift
            ;;
        --coverage)
            COVERAGE="--coverage-html ${REPORT_DIR}/coverage"
            shift
            ;;
        --parallel)
            PARALLEL="--parallel"
            shift
            ;;
        --sync)
            FORCE_SYNC="true"
            PARALLEL=""
            shift
            ;;
        --wait)
            SHOULD_WAIT="true"
            shift
            ;;
        --timeout)
            LOCK_TIMEOUT="$2"
            shift 2
            ;;
        --help)
            show_help
            exit 0
            ;;
        *)
            echo "Unknown option: $1"
            show_help
            exit 1
            ;;
    esac
done

# Acquire lock if wait is requested
if [ -n "$SHOULD_WAIT" ]; then
    if ! acquire_lock "$LOCK_TIMEOUT"; then
        echo -e "${RED}✗ Failed to acquire lock${NC}"
        exit 1
    fi
fi

echo -e "${YELLOW}Test Configuration:${NC}"
echo -e "  Suite: ${TEST_SUITE}"
echo -e "  Coverage: $([ -n "$COVERAGE" ] && echo "Enabled" || echo "Disabled")"
echo -e "  Parallel: $([ -n "$PARALLEL" ] && echo "Enabled" || echo "Disabled")"
echo -e "  Synchronous: $([ -n "$FORCE_SYNC" ] && echo "Forced" || echo "Auto")"
echo -e "  Report: ${HTML_REPORT}"
echo ""

# Run tests with synchronization
echo -e "${BLUE}Running tests synchronously...${NC}"
echo ""

TEST_EXIT_CODE=0
PIDS=()

# Function to run test and wait
run_test_sync() {
    local name=$1
    local command=$2

    echo -e "${BLUE}→ Running ${name}...${NC}"

    if eval "$command"; then
        echo -e "${GREEN}✓ ${name} passed${NC}"
        return 0
    else
        echo -e "${RED}✗ ${name} failed${NC}"
        return 1
    fi
}

# Execute tests based on suite selection
case $TEST_SUITE in
    browser)
        if [ -d "tests/Browser" ]; then
            run_test_sync "Browser Tests" "php artisan dusk --log-junit ${JSON_REPORT}"
            TEST_EXIT_CODE=$?
        else
            echo -e "${YELLOW}No browser tests found. Skipping...${NC}"
        fi
        ;;
    feature)
        run_test_sync "Feature Tests" "vendor/bin/phpunit --testsuite Feature --log-junit ${JSON_REPORT} ${COVERAGE}"
        TEST_EXIT_CODE=$?
        ;;
    unit)
        run_test_sync "Unit Tests" "vendor/bin/phpunit --testsuite Unit --log-junit ${JSON_REPORT} ${COVERAGE}"
        TEST_EXIT_CODE=$?
        ;;
    all)
        # Run in sequence for synchronous execution
        if [ -n "$FORCE_SYNC" ] || [ -z "$PARALLEL" ]; then
            echo -e "${YELLOW}Running tests in sequence (synchronized)${NC}"

            run_test_sync "Unit Tests" "vendor/bin/phpunit --testsuite Unit ${COVERAGE}"
            UNIT_EXIT=$?

            run_test_sync "Feature Tests" "vendor/bin/phpunit --testsuite Feature ${COVERAGE}"
            FEATURE_EXIT=$?

            if [ -d "tests/Browser" ]; then
                run_test_sync "Browser Tests" "php artisan dusk"
                BROWSER_EXIT=$?
            else
                BROWSER_EXIT=0
            fi

            # Calculate overall exit code
            TEST_EXIT_CODE=$((UNIT_EXIT + FEATURE_EXIT + BROWSER_EXIT))
        else
            # Parallel execution with wait
            echo -e "${YELLOW}Running tests in parallel (with synchronization points)${NC}"

            vendor/bin/phpunit --testsuite Unit ${COVERAGE} ${PARALLEL} &
            PIDS+=($!)

            vendor/bin/phpunit --testsuite Feature ${COVERAGE} ${PARALLEL} &
            PIDS+=($!)

            # Wait for PHPUnit tests to complete before starting Dusk
            echo -e "${YELLOW}Waiting for unit and feature tests...${NC}"
            for pid in "${PIDS[@]}"; do
                if ! wait "$pid"; then
                    TEST_EXIT_CODE=1
                fi
            done

            # Run Dusk tests after PHPUnit completes
            if [ -d "tests/Browser" ]; then
                echo -e "${BLUE}Starting browser tests...${NC}"
                run_test_sync "Browser Tests" "php artisan dusk"
                BROWSER_EXIT=$?
                TEST_EXIT_CODE=$((TEST_EXIT_CODE + BROWSER_EXIT))
            fi
        fi
        ;;
esac

echo ""
echo -e "${BLUE}============================================${NC}"

# Generate HTML report with synchronization
echo -e "${YELLOW}Generating HTML report (synchronized)...${NC}"

php artisan test:generate-report --output="${HTML_REPORT}" --format=html

# Wait for report generation to complete
sync

# Create symlink to latest report
ln -sf "test-report-${TIMESTAMP}.html" "${LATEST_REPORT}"

echo -e "${GREEN}Report generated: ${HTML_REPORT}${NC}"
echo -e "${GREEN}Latest report: ${LATEST_REPORT}${NC}"

# Display test results summary
if [ $TEST_EXIT_CODE -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✓ All tests passed!${NC}"
    echo -e "${BLUE}============================================${NC}"
    exit 0
else
    echo ""
    echo -e "${RED}✗ Some tests failed!${NC}"
    echo -e "${BLUE}============================================${NC}"
    exit 1
fi
