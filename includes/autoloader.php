<?php
/**
 * Custom Class Autoloader
 * 
 * No Composer required - simple autoloader for classes directory
 */

spl_autoload_register(function ($className) {
    // Convert namespace to directory path
    $baseDir = __DIR__ . '/../classes/';
    $file = $baseDir . $className . '.php';
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    return false;
});

