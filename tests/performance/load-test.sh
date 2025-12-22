#!/bin/bash
# Load Testing Script
# Uses Apache Bench (ab), k6, or JMeter

BASE_URL="http://localhost/cricapp"

echo "=========================================="
echo "Load Testing for Cricket App"
echo "=========================================="
echo ""

# Check for Apache Bench
if command -v ab &> /dev/null; then
    echo "1. Running Apache Bench tests..."
    echo ""
    
    echo "   Testing match list endpoint..."
    ab -n 100 -c 10 "${BASE_URL}/api/v1/matches.php"
    echo ""
    
    echo "   Testing authentication endpoint..."
    ab -n 50 -c 5 -p auth-post.json -T application/json "${BASE_URL}/api/v1/auth.php"
    echo ""
else
    echo "Apache Bench (ab) not found."
    echo "Install via: sudo apt-get install apache2-utils"
    echo ""
fi

# Check for k6
if command -v k6 &> /dev/null; then
    echo "2. Running k6 load tests..."
    k6 run load-test.js
    echo ""
else
    echo "k6 not found."
    echo "Install via: https://k6.io/docs/getting-started/installation/"
    echo ""
fi

echo "=========================================="
echo "Load Testing Complete"
echo "=========================================="
echo ""
echo "For comprehensive load testing, use:"
echo "  - Apache Bench (ab): Simple HTTP benchmarking"
echo "  - k6: Modern load testing tool"
echo "  - Apache JMeter: Full-featured load testing"
echo ""
echo "Target metrics:"
echo "  - Response time: < 1 second"
echo "  - Concurrent users: 100+"
echo "  - Requests/second: 50+"



