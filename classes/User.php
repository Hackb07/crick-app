<?php
/**
 * User Authentication and Management
 * 
 * Manages user data, authentication, and authorization.
 * Extends BaseModel to eliminate code duplication.
 */

class User extends BaseModel {
    /** @var string Table name */
    protected $tableName = 'users';
    
    /** @var string Primary key column */
    protected $primaryKey = 'user_id';
    
    /**
     * Constructor
     * 
     * Accepts PDO connection via dependency injection.
     * Falls back to Database singleton if not provided (for backward compatibility).
     * 
     * @param PDO|null $db Database connection (optional, for dependency injection)
     */
    public function __construct(?PDO $db = null) {
        parent::__construct($db);
    }
    
    /**
     * Authenticate user with username and password
     * 
     * @param string $username Username
     * @param string $password Password
     * @return array|null User data or null if invalid
     */
    public function authenticate($username, $password) {
        $sql = "SELECT user_id, username, email, password_hash, role, full_name, is_active 
                FROM {$this->tableName} 
                WHERE username = :username AND is_active = 1 
                LIMIT 1";
        
        try {
            $stmt = $this->executeQuery($sql, ['username' => $username]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return null;
            }
            
            if (!password_verify($password, $user['password_hash'])) {
                return null;
            }
            
            // Remove password hash from response
            unset($user['password_hash']);
            
            return $user;
        } catch (PDOException $e) {
            error_log('Authentication error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get user by ID
     * 
     * Overrides base method to exclude password hash from response.
     * 
     * @param int $userId User ID
     * @return array|null User data or null
     */
    public function getById(int $userId): ?array {
        $sql = "SELECT user_id, username, email, role, full_name, is_active, created_at, updated_at 
                FROM {$this->tableName} 
                WHERE {$this->primaryKey} = :user_id 
                LIMIT 1";
        
        $stmt = $this->executeQuery($sql, ['user_id' => $userId]);
        $user = $stmt->fetch();
        
        return $user ?: null;
    }
    
    /**
     * Get user by username
     * 
     * @param string $username Username
     * @return array|null User data or null
     */
    public function getByUsername($username) {
        $sql = "SELECT user_id, username, email, role, full_name, is_active, created_at, updated_at 
                FROM {$this->tableName} 
                WHERE username = :username 
                LIMIT 1";
        
        $stmt = $this->executeQuery($sql, ['username' => $username]);
        $user = $stmt->fetch();
        
        return $user ?: null;
    }
    
    /**
     * Create new user
     * 
     * @param array $data User data (username, password, email, role, full_name, is_active)
     * @return int|false User ID or false on failure
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->tableName} (username, email, password_hash, role, full_name, is_active, created_at, updated_at) 
                VALUES (:username, :email, :password_hash, :role, :full_name, :is_active, NOW(), NOW())";
        
        $passwordHash = password_hash($data['password'], PASSWORD_ARGON2ID);
        
        try {
            $stmt = $this->executeQuery($sql, [
                'username' => $data['username'],
                'email' => $data['email'] ?? '',
                'password_hash' => $passwordHash,
                'role' => $data['role'] ?? 'user',
                'full_name' => $data['full_name'] ?? '',
                'is_active' => $data['is_active'] ?? 1
            ]);
            
            return (int)$this->getDb()->lastInsertId();
        } catch (PDOException $e) {
            error_log('Failed to create user: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all users
     * 
     * Overrides base method to provide custom filtering and exclude password hash.
     * 
     * @param array $filters Filter options (role, is_active)
     * @return array List of users
     */
    public function getAll(array $filters = []): array {
        $sql = "SELECT user_id, username, email, role, full_name, is_active, created_at, updated_at 
                FROM {$this->tableName} 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['role'])) {
            $sql .= " AND role = :role";
            $params['role'] = $filters['role'];
        }
        
        if (isset($filters['is_active'])) {
            $sql .= " AND is_active = :is_active";
            $params['is_active'] = (int)$filters['is_active'];
        }
        
        // Sorting
        if (isset($filters['sort'])) {
            switch ($filters['sort']) {
                case 'username_asc':
                    $sql .= " ORDER BY username ASC";
                    break;
                case 'username_desc':
                    $sql .= " ORDER BY username DESC";
                    break;
                case 'created_asc':
                    $sql .= " ORDER BY created_at ASC";
                    break;
                default:
                    $sql .= " ORDER BY created_at DESC";
            }
        } else {
            $sql .= " ORDER BY created_at DESC";
        }
        
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Update user
     * 
     * @param int $userId User ID
     * @param array $data User data to update
     * @return bool Success
     */
    public function update($userId, $data) {
        $updateFields = [];
        $params = ['user_id' => $userId];
        
        if (isset($data['email'])) {
            $updateFields[] = "email = :email";
            $params['email'] = $data['email'];
        }
        
        if (isset($data['role'])) {
            $updateFields[] = "role = :role";
            $params['role'] = $data['role'];
        }
        
        if (isset($data['full_name'])) {
            $updateFields[] = "full_name = :full_name";
            $params['full_name'] = $data['full_name'];
        }
        
        if (isset($data['is_active'])) {
            $updateFields[] = "is_active = :is_active";
            $params['is_active'] = (int)$data['is_active'];
        }
        
        if (isset($data['password'])) {
            $updateFields[] = "password_hash = :password_hash";
            $params['password_hash'] = password_hash($data['password'], PASSWORD_ARGON2ID);
        }
        
        if (empty($updateFields)) {
            return false;
        }
        
        $updateFields[] = "updated_at = NOW()";
        $sql = "UPDATE {$this->tableName} SET " . implode(', ', $updateFields) . " WHERE {$this->primaryKey} = :user_id";
        
        try {
            $this->executeQuery($sql, $params);
            return true;
        } catch (PDOException $e) {
            error_log('Failed to update user: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete user
     * 
     * @param int $userId User ID
     * @return bool Success
     */
    public function delete($userId) {
        // Prevent deleting own account
        if (function_exists('getUserId') && getUserId() == $userId) {
            return false;
        }
        
        $sql = "DELETE FROM {$this->tableName} WHERE {$this->primaryKey} = :user_id";
        try {
            $this->executeQuery($sql, ['user_id' => $userId]);
            return true;
        } catch (PDOException $e) {
            error_log('Failed to delete user: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user password
     * 
     * @param int $userId User ID
     * @param string $newPassword New password
     * @return bool Success
     */
    public function updatePassword($userId, $newPassword) {
        $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
        
        $sql = "UPDATE {$this->tableName} 
                SET password_hash = :password_hash, updated_at = NOW() 
                WHERE {$this->primaryKey} = :user_id";
        
        try {
            $this->executeQuery($sql, [
                'user_id' => $userId,
                'password_hash' => $passwordHash
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('Failed to update password: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user has required role
     * 
     * @param array $user User data
     * @param string|array $requiredRole Required role(s)
     * @return bool Has permission
     */
    public static function hasRole($user, $requiredRole) {
        if (!isset($user['role'])) {
            return false;
        }
        
        if (is_array($requiredRole)) {
            return in_array($user['role'], $requiredRole);
        }
        
        return $user['role'] === $requiredRole;
    }
}

