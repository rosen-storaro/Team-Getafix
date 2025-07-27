<?php
declare(strict_types=1);

/**
 * Database Configuration and Connection Pool
 * Provides singleton PDO connections for each database
 */

class DatabasePool {
    private static array $connections = [];
    
    /**
     * Get database connection for specified database
     */
    public static function getConnection(string $database): PDO {
        if (!isset(self::$connections[$database])) {
            self::$connections[$database] = self::createConnection($database);
        }
        
        // Test connection and reconnect if needed
        try {
            self::$connections[$database]->query('SELECT 1');
        } catch (PDOException $e) {
            self::$connections[$database] = self::createConnection($database);
        }
        
        return self::$connections[$database];
    }
    
    /**
     * Create new PDO connection
     */
    private static function createConnection(string $database): PDO {
        $host = env('DB_HOST');
        $port = env('DB_PORT', '3306');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        
        $dbName = match($database) {
            'auth' => env('AUTH_DB_NAME'),
            'inventory' => env('INVENTORY_DB_NAME'),
            'reports' => env('REPORTS_DB_NAME'),
            default => throw new InvalidArgumentException("Unknown database: {$database}")
        };
        
        $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true, // Connection pooling
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        try {
            $pdo = new PDO($dsn, $username, $password, $options);
            
            // Set SQL mode for strict data integrity
            $pdo->exec("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
            
            return $pdo;
        } catch (PDOException $e) {
            error_log("Database connection failed for {$database}: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    /**
     * Close all connections
     */
    public static function closeAll(): void {
        self::$connections = [];
    }
}

/**
 * Base Model class with database connection
 */
abstract class BaseModel {
    protected PDO $db;
    protected string $table;
    
    public function __construct(string $database) {
        $this->db = DatabasePool::getConnection($database);
    }
    
    /**
     * Execute prepared statement with parameters
     */
    protected function execute(string $sql, array $params = []): PDOStatement {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("SQL Error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Database operation failed");
        }
    }
    
    /**
     * Get single record by ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->execute("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Get all records with optional conditions
     */
    public function findAll(array $conditions = [], string $orderBy = 'id ASC', int $limit = 0): array {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $field => $value) {
                $whereClause[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $sql .= " ORDER BY {$orderBy}";
        
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Insert new record
     */
    public function create(array $data): int {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->execute($sql, array_values($data));
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Update record by ID
     */
    public function update(int $id, array $data): bool {
        $fields = array_keys($data);
        $setClause = array_map(fn($field) => "{$field} = ?", $fields);
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE id = ?";
        
        $params = array_values($data);
        $params[] = $id;
        
        $stmt = $this->execute($sql, $params);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Delete record by ID
     */
    public function delete(int $id): bool {
        $stmt = $this->execute("DELETE FROM {$this->table} WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Count records with optional conditions
     */
    public function count(array $conditions = []): int {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $field => $value) {
                $whereClause[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $stmt = $this->execute($sql, $params);
        return (int) $stmt->fetchColumn();
    }
}

