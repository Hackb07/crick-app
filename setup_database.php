<?php
/**
 * Database Setup Script
 * Run this once to create and populate the database
 */

require_once __DIR__ . '/includes/config.php';

echo "Creating and setting up database...\n\n";

try {
    // Connect without database first
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if not exists
    echo "1. Creating database 'cricapp'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   ✓ Database created/verified\n\n";

    // Select database
    $pdo->exec("USE `" . DB_NAME . "`");
    echo "2. Importing schema...\n";

    // Read and execute schema file
    $schemaFile = __DIR__ . '/sql/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }

    $schema = file_get_contents($schemaFile);
    
    // Remove SET commands that might cause issues
    $schema = preg_replace('/^SET[^;]*;$/m', '', $schema);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    $executed = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            // Skip if table already exists or other non-critical errors
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate key') === false) {
                echo "   Warning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "   ✓ Executed $executed SQL statements\n\n";

    // Import seeds
    echo "3. Importing initial data (seeds)...\n";
    $seedsFile = __DIR__ . '/sql/seeds.sql';
    if (file_exists($seedsFile)) {
        $seeds = file_get_contents($seedsFile);
        $seedStatements = array_filter(array_map('trim', explode(';', $seeds)));
        
        foreach ($seedStatements as $statement) {
            if (empty($statement) || strpos($statement, '--') === 0) {
                continue;
            }
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Ignore duplicate entry errors
                if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                    echo "   Warning: " . $e->getMessage() . "\n";
                }
            }
        }
        echo "   ✓ Seeds imported\n\n";
    } else {
        echo "   ⚠ Seeds file not found, skipping...\n\n";
    }

    // Verify tables were created
    echo "4. Verifying tables...\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "   ✓ Found " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        echo "      - $table\n";
    }
    
    echo "\n✅ Database setup completed successfully!\n\n";
    echo "Default login credentials:\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n";
    echo "\n⚠ IMPORTANT: Change the admin password after first login!\n";

} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}




