<?php
/**
 * GreenGuard — JSON Data Store & Database Engine
 * 
 * Provides thread-safe, structured CRUD operations for JSON storage.
 */

if (file_exists(__DIR__ . '/../config/config.php')) {
    require_once __DIR__ . '/../config/config.php';
} elseif (file_exists(__DIR__ . '/../config/config.example.php')) {
    require_once __DIR__ . '/../config/config.example.php';
}

if (!defined('DATA_PATH')) {
    define('DATA_PATH', __DIR__ . '/../data/');
}

class DB {
    private static function getFilePath(string $table): string {
        return DATA_PATH . $table . '.json';
    }

    /**
     * Read all records from a table with a shared lock
     */
    public static function all(string $table): array {
        $file = self::getFilePath($table);
        if (!file_exists($file)) {
            return [];
        }

        $fp = fopen($file, 'r');
        if (!$fp) return [];

        flock($fp, LOCK_SH);
        $size = filesize($file);
        $content = $size > 0 ? fread($fp, $size) : '[]';
        flock($fp, LOCK_UN);
        fclose($fp);

        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Write records to a table with an exclusive lock
     */
    public static function write(string $table, array $data): bool {
        $file = self::getFilePath($table);
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fp = fopen($file, 'c+');
        if (!$fp) return false;

        if (flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);
            rewind($fp);
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            fwrite($fp, $json);
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            return true;
        }

        fclose($fp);
        return false;
    }

    /**
     * Find a single record matching criteria
     */
    public static function findOne(string $table, callable $predicate): ?array {
        $records = self::all($table);
        foreach ($records as $record) {
            if ($predicate($record)) {
                return $record;
            }
        }
        return null;
    }

    /**
     * Find a record by a primary key column
     */
    public static function findById(string $table, $id, string $idKey = 'id'): ?array {
        return self::findOne($table, fn($r) => isset($r[$idKey]) && (string)$r[$idKey] === (string)$id);
    }

    /**
     * Filter records by criteria
     */
    public static function filter(string $table, callable $predicate): array {
        $records = self::all($table);
        return array_values(array_filter($records, $predicate));
    }

    /**
     * Insert a new record with auto-increment ID
     */
    public static function insert(string $table, array $record, string $idKey = 'id'): array {
        $records = self::all($table);
        
        // Auto-increment primary key if not set
        if (!isset($record[$idKey]) || empty($record[$idKey])) {
            $maxId = 0;
            foreach ($records as $r) {
                if (isset($r[$idKey]) && is_numeric($r[$idKey]) && $r[$idKey] > $maxId) {
                    $maxId = (int)$r[$idKey];
                }
            }
            // For reports start at 1001, users at 1, others at 1
            $start = ($table === 'reports') ? 1000 : 0;
            $record[$idKey] = max($maxId + 1, $start + 1);
        }

        if (!isset($record['created_at'])) {
            $record['created_at'] = date('Y-m-d H:i:s');
        }

        $records[] = $record;
        self::write($table, $records);
        return $record;
    }

    /**
     * Update an existing record matching an ID
     */
    public static function update(string $table, $id, array $updates, string $idKey = 'id'): ?array {
        $records = self::all($table);
        $updatedRecord = null;

        foreach ($records as &$record) {
            if (isset($record[$idKey]) && (string)$record[$idKey] === (string)$id) {
                $updates['updated_at'] = date('Y-m-d H:i:s');
                $record = array_merge($record, $updates);
                $updatedRecord = $record;
                break;
            }
        }
        unset($record);

        if ($updatedRecord !== null) {
            self::write($table, $records);
        }

        return $updatedRecord;
    }

    /**
     * Delete a record by ID
     */
    public static function delete(string $table, $id, string $idKey = 'id'): bool {
        $records = self::all($table);
        $initialCount = count($records);
        
        $records = array_values(array_filter($records, fn($r) => !(isset($r[$idKey]) && (string)$r[$idKey] === (string)$id)));

        if (count($records) < $initialCount) {
            self::write($table, $records);
            return true;
        }

        return false;
    }
}
