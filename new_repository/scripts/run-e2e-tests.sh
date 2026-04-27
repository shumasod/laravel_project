#!/bin/bash
#
# E2E Test Execution Script with HTML Report Generation
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

echo -e "${BLUE}============================================${NC}"
echo -e "${BLUE}  E2E Test Suite Execution${NC}"
echo -e "${BLUE}============================================${NC}"
echo ""

# Create report directory
mkdir -p "${REPORT_DIR}"

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
    echo "  --help           Show this help message"
    echo ""
}

# Parse arguments
TEST_SUITE="all"
COVERAGE=""
PARALLEL=""

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

echo -e "${YELLOW}Test Configuration:${NC}"
echo -e "  Suite: ${TEST_SUITE}"
echo -e "  Coverage: $([ -n "$COVERAGE" ] && echo "Enabled" || echo "Disabled")"
echo -e "  Parallel: $([ -n "$PARALLEL" ] && echo "Enabled" || echo "Disabled")"
echo -e "  Report: ${HTML_REPORT}"
echo ""

# Run tests based on suite selection
echo -e "${BLUE}Running tests...${NC}"
echo ""

case $TEST_SUITE in
    browser)
        if [ -d "tests/Browser" ]; then
            php artisan dusk --log-junit "${JSON_REPORT}" ${PARALLEL}
        else
            echo -e "${YELLOW}No browser tests found. Skipping...${NC}"
        fi
        ;;
    feature)
        vendor/bin/phpunit --testsuite Feature --log-junit "${JSON_REPORT}" ${COVERAGE} ${PARALLEL}
        ;;
    unit)
        vendor/bin/phpunit --testsuite Unit --log-junit "${JSON_REPORT}" ${COVERAGE} ${PARALLEL}
        ;;
    all)
        # Run PHPUnit tests
        vendor/bin/phpunit --log-junit "${JSON_REPORT}" ${COVERAGE} ${PARALLEL}

        # Run Dusk tests if they exist
        if [ -d "tests/Browser" ]; then
            echo ""
            echo -e "${BLUE}Running browser tests...${NC}"
            php artisan dusk ${PARALLEL}
        fi
        ;;
esac

TEST_EXIT_CODE=$?

echo ""
echo -e "${BLUE}============================================${NC}"

# Generate HTML report
echo -e "${YELLOW}Generating HTML report...${NC}"

php artisan test:generate-report --output="${HTML_REPORT}"

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
