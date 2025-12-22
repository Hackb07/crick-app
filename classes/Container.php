<?php
/**
 * Simple Dependency Injection Container
 * 
 * Provides dependency injection for models and services.
 * Eliminates direct instantiation and enables easier testing.
 * 
 * Usage:
 *   $container = Container::getInstance();
 *   $userModel = $container->get('User');
 *   $matchModel = $container->get('MatchModel');
 */
class Container {
    /** @var Container Singleton instance */
    private static $instance = null;
    
    /** @var array Service instances cache */
    private $instances = [];
    
    /** @var array Service factories */
    private $factories = [];
    
    /**
     * Private constructor (singleton pattern)
     */
    private function __construct() {
        $this->registerDefaultServices();
    }
    
    /**
     * Get singleton instance
     * 
     * @return Container
     */
    public static function getInstance(): Container {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Register default services
     * 
     * Registers all model classes and common services.
     */
    private function registerDefaultServices(): void {
        // Register database connection
        $this->factories['Database'] = function() {
            return Database::getInstance()->getConnection();
        };
        
        // Register models with database injection
        $models = ['User', 'Team', 'Player', 'Series', 'MatchModel', 'Event', 'ActionLogger', 'RateLimiter'];
        foreach ($models as $model) {
            $this->factories[$model] = function() use ($model) {
                $db = $this->get('Database');
                return new $model($db);
            };
        }
        
        // Register repositories
        $repositories = ['MatchRepository', 'EventRepository'];
        foreach ($repositories as $repo) {
            $this->factories[$repo] = function() use ($repo) {
                $db = $this->get('Database');
                return new $repo($db);
            };
        }
        
        // Register services
        $this->factories['MatchService'] = function() {
            $db = $this->get('Database');
            $matchRepo = $this->get('MatchRepository');
            return new MatchService($matchRepo);
        };
        
        $this->factories['MatchStateMachine'] = function() {
            $db = $this->get('Database');
            return new MatchStateMachine($db);
        };
    }
    
    /**
     * Get service instance
     * 
     * Returns cached instance if available, otherwise creates new instance.
     * 
     * @param string $name Service name (class name)
     * @return mixed Service instance
     * @throws RuntimeException If service not found
     */
    public function get(string $name) {
        // Return cached instance if available
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }
        
        // Create instance using factory
        if (isset($this->factories[$name])) {
            $instance = $this->factories[$name]();
            $this->instances[$name] = $instance;
            return $instance;
        }
        
        // Fallback: try to instantiate class directly (for backward compatibility)
        if (class_exists($name)) {
            $instance = new $name();
            $this->instances[$name] = $instance;
            return $instance;
        }
        
        throw new RuntimeException("Service '{$name}' not found in container");
    }
    
    /**
     * Register a service factory
     * 
     * @param string $name Service name
     * @param callable $factory Factory function that returns service instance
     */
    public function register(string $name, callable $factory): void {
        $this->factories[$name] = $factory;
        // Clear cached instance if exists
        unset($this->instances[$name]);
    }
    
    /**
     * Check if service is registered
     * 
     * @param string $name Service name
     * @return bool True if registered
     */
    public function has(string $name): bool {
        return isset($this->factories[$name]) || class_exists($name);
    }
    
    /**
     * Clear all cached instances
     * 
     * Useful for testing or when services need to be recreated.
     */
    public function clear(): void {
        $this->instances = [];
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

