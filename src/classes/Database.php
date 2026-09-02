<?php
/**
 * Database Connection Class
 */

namespace App\Classes;

class Database {
    private $connection;
    private static $instance = null;

    private function __construct() {
        $this->connection = require __DIR__ . '/../../config/database.php';
    }

    /**
     * Get database instance (Singleton pattern)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get connection
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Execute a query
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            
            if (!$stmt) {
                throw new \Exception('Prepare statement error: ' . $this->connection->error);
            }

            if (!empty($params)) {
                $types = '';
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } else {
                        $types .= 's';
                    }
                }
                $stmt->bind_param($types, ...$params);
            }

            if (!$stmt->execute()) {
                throw new \Exception('Execute error: ' . $stmt->error);
            }

            return $stmt;
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                throw $e;
            }
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * Get single row
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if (!$stmt) return null;

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row;
    }

    /**
     * Get multiple rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if (!$stmt) return [];

        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();

        return $rows;
    }

    /**
     * Get last inserted ID
     */
    public function lastInsertId() {
        return $this->connection->insert_id;
    }

    /**
     * Close connection
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
