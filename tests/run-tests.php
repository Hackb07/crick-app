<?php
/**
 * Test Runner Script
 * Runs all test suites and generates reports
 */

require_once __DIR__ . '/bootstrap.php';

class TestRunner {
    private $baseDir;
    private $testDir;
    private $results = [];
    
    public function __construct() {
        $this->baseDir = dirname(__DIR__);
        $this->testDir = __DIR__;
    }
    
    /**
     * Run all test suites
     */
    public function runAll() {
        echo "========================================\n";
        echo "Cricket App Test Suite Runner\n";
        echo "========================================\n\n";
        
        $suites = [
            'Functional' => 'tests/functional',
            'Integration' => 'tests/integration',
            'Security' => 'tests/security',
            'Performance' => 'tests/performance',
            'Regression' => 'tests/regression'
        ];
        
        $totalTests = 0;
        $totalPassed = 0;
        $totalFailed = 0;
        
        foreach ($suites as $name => $path) {
            echo "Running $name Tests...\n";
            echo str_repeat('-', 40) . "\n";
            
            $result = $this->runSuite($name, $path);
            $this->results[$name] = $result;
            
            $totalTests += $result['total'];
            $totalPassed += $result['passed'];
            $totalFailed += $result['failed'];
            
            echo "  Total: {$result['total']}\n";
            echo "  Passed: {$result['passed']}\n";
            echo "  Failed: {$result['failed']}\n";
            echo "\n";
        }
        
        // Summary
        echo "========================================\n";
        echo "Test Summary\n";
        echo "========================================\n";
        echo "Total Tests: $totalTests\n";
        echo "Passed: $totalPassed\n";
        echo "Failed: $totalFailed\n";
        echo "Success Rate: " . ($totalTests > 0 ? round(($totalPassed / $totalTests) * 100, 2) : 0) . "%\n";
        echo "\n";
        
        // Generate report
        $this->generateReport();
        
        return $totalFailed === 0;
    }
    
    /**
     * Run a test suite
     */
    private function runSuite($name, $path) {
        // Check if PHPUnit is available
        $phpunitPath = $this->findPHPUnit();
        
        if (!$phpunitPath) {
            echo "  ⚠ PHPUnit not found. Install via: composer install\n";
            return ['total' => 0, 'passed' => 0, 'failed' => 0];
        }
        
        $fullPath = $this->baseDir . '/' . $path;
        $phpunitXml = $this->baseDir . '/phpunit.xml';
        
        $command = "$phpunitPath --configuration $phpunitXml --testsuite $name --testdox";
        
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        // Parse output
        $passed = 0;
        $failed = 0;
        $total = 0;
        
        foreach ($output as $line) {
            echo "  $line\n";
            if (preg_match('/(\d+) \/ (\d+)/', $line, $matches)) {
                $passed = (int)$matches[1];
                $total = (int)$matches[2];
                $failed = $total - $passed;
            }
        }
        
        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'output' => implode("\n", $output),
            'success' => $returnCode === 0
        ];
    }
    
    /**
     * Find PHPUnit executable
     */
    private function findPHPUnit() {
        // Check vendor/bin
        $vendorPath = $this->baseDir . '/vendor/bin/phpunit';
        if (file_exists($vendorPath)) {
            return $vendorPath;
        }
        
        // Check global installation
        $which = '';
        exec('which phpunit 2>/dev/null', $which);
        if (!empty($which)) {
            return $which[0];
        }
        
        return null;
    }
    
    /**
     * Generate test report
     */
    private function generateReport() {
        $reportDir = $this->baseDir . '/tests/reports';
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }
        
        $reportFile = $reportDir . '/test-report-' . date('Y-m-d-H-i-s') . '.json';
        
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'suites' => $this->results,
            'summary' => [
                'total' => array_sum(array_column($this->results, 'total')),
                'passed' => array_sum(array_column($this->results, 'passed')),
                'failed' => array_sum(array_column($this->results, 'failed'))
            ]
        ];
        
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
        
        echo "Test report saved to: $reportFile\n";
        
        // Also generate HTML report
        $this->generateHTMLReport($report, $reportDir);
    }
    
    /**
     * Generate HTML report
     */
    private function generateHTMLReport($report, $reportDir) {
        $htmlFile = $reportDir . '/test-report-' . date('Y-m-d-H-i-s') . '.html';
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Test Report - ' . htmlspecialchars($report['timestamp']) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .summary { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .suite { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .passed { color: green; }
        .failed { color: red; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #4CAF50; color: white; }
    </style>
</head>
<body>
    <h1>Test Report</h1>
    <p><strong>Generated:</strong> ' . htmlspecialchars($report['timestamp']) . '</p>
    
    <div class="summary">
        <h2>Summary</h2>
        <p><strong>Total Tests:</strong> ' . $report['summary']['total'] . '</p>
        <p class="passed"><strong>Passed:</strong> ' . $report['summary']['passed'] . '</p>
        <p class="failed"><strong>Failed:</strong> ' . $report['summary']['failed'] . '</p>
        <p><strong>Success Rate:</strong> ' . ($report['summary']['total'] > 0 ? round(($report['summary']['passed'] / $report['summary']['total']) * 100, 2) : 0) . '%</p>
    </div>
    
    <h2>Test Suites</h2>';
        
        foreach ($report['suites'] as $suiteName => $suiteData) {
            $statusClass = $suiteData['success'] ? 'passed' : 'failed';
            $html .= '
    <div class="suite">
        <h3>' . htmlspecialchars($suiteName) . ' - <span class="' . $statusClass . '">' . ($suiteData['success'] ? 'PASSED' : 'FAILED') . '</span></h3>
        <p>Total: ' . $suiteData['total'] . ' | Passed: ' . $suiteData['passed'] . ' | Failed: ' . $suiteData['failed'] . '</p>
        <pre style="background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto;">' . htmlspecialchars($suiteData['output'] ?? '') . '</pre>
    </div>';
        }
        
        $html .= '
</body>
</html>';
        
        file_put_contents($htmlFile, $html);
        echo "HTML report saved to: $htmlFile\n";
    }
}

// Run tests
$runner = new TestRunner();
$success = $runner->runAll();

exit($success ? 0 : 1);



