<?php
require_once (__DIR__ . '/../config.php');

class TodoModel
{
    private $conn;

    public function __construct()
    {
        // Inisialisasi koneksi database PostgreSQL
        $this->conn = pg_connect('host=' . DB_HOST . ' port=' . DB_PORT . ' dbname=' . DB_NAME . ' user=' . DB_USER . ' password=' . DB_PASSWORD);
        if (!$this->conn) {
            die('Koneksi database gagal');
        }
    }

    public function getAllTodos()
    {
        $query = 'SELECT * FROM todo ORDER BY created_at DESC';
        $result = pg_query($this->conn, $query);
        $todos = [];
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                // Normalize is_finished value to boolean for consistent view logic
                $row['is_finished'] = $this->normalizeIsFinishedValue($row['is_finished']);
                $todos[] = $row;
            }
        }
        return $todos;
    }

    public function getFilteredTodos($filter = 'all')
    {
        // Build a robust WHERE clause that works whether is_finished is stored as
        // boolean (true/false), text ('t'/'f'), or numeric (1/0).
        $query = 'SELECT * FROM todo';
        if ($filter === 'finished') {
            $query .= " WHERE (is_finished = true OR is_finished = 't' OR is_finished = '1')";
        } elseif ($filter === 'unfinished') {
            $query .= " WHERE (is_finished = false OR is_finished = 'f' OR is_finished = '0')";
        }
        $query .= ' ORDER BY created_at DESC';
        $result = pg_query($this->conn, $query);
        $todos = [];
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $row['is_finished'] = $this->normalizeIsFinishedValue($row['is_finished']);
                $todos[] = $row;
            }
        }
        return $todos;
    }

    public function searchTodos($search, $filter = 'all')
    {
        $query = 'SELECT * FROM todo WHERE title ILIKE $1';
        $params = ['%' . $search . '%'];
        if ($filter === 'finished') {
            $query .= " AND (is_finished = true OR is_finished = 't' OR is_finished = '1')";
        } elseif ($filter === 'unfinished') {
            $query .= " AND (is_finished = false OR is_finished = 'f' OR is_finished = '0')";
        }
        $query .= ' ORDER BY created_at DESC';
        $result = pg_query_params($this->conn, $query, $params);
        $todos = [];
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $row['is_finished'] = $this->normalizeIsFinishedValue($row['is_finished']);
                $todos[] = $row;
            }
        }
        return $todos;
    }

    public function checkTitleExists($title, $excludeId = null)
    {
        $query = 'SELECT COUNT(*) as count FROM todo WHERE title = $1';
        $params = [$title];
        if ($excludeId) {
            $query .= ' AND id != $2';
            $params[] = $excludeId;
        }
        $result = pg_query_params($this->conn, $query, $params);
        $row = pg_fetch_assoc($result);
        return $row['count'] > 0;
    }

    public function getTodoById($id)
    {
        $query = 'SELECT * FROM todo WHERE id = $1';
        $result = pg_query_params($this->conn, $query, [$id]);
        if ($result && pg_num_rows($result) > 0) {
            $row = pg_fetch_assoc($result);
            $row['is_finished'] = $this->normalizeIsFinishedValue($row['is_finished']);
            return $row;
        }
        return null;
    }

    public function createTodo($title, $description = '')
    {
        if ($this->checkTitleExists($title)) {
            return false; // Title already exists
        }
        $query = 'INSERT INTO todo (title, description, is_finished, created_at, updated_at) VALUES ($1, $2, false, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)';
        $result = pg_query_params($this->conn, $query, [$title, $description]);
        return $result !== false;
    }

    public function updateTodo($id, $title, $description, $isFinished)
    {
        if ($this->checkTitleExists($title, $id)) {
            return false; // Title already exists for another todo
        }
        $query = 'UPDATE todo SET title=$1, description=$2, is_finished=$3, updated_at=CURRENT_TIMESTAMP WHERE id=$4';
        $result = pg_query_params($this->conn, $query, [$title, $description, $isFinished, $id]);
        return $result !== false;
    }

    public function deleteTodo($id)
    {
        $query = 'DELETE FROM todo WHERE id=$1';
        $result = pg_query_params($this->conn, $query, [$id]);
        return $result !== false;
    }

    /**
     * Normalize various stored representations of is_finished to boolean.
     * Accepts boolean, 't'/'f', '1'/'0', 'true'/'false', and numeric values.
     */
    private function normalizeIsFinishedValue($val)
    {
        if (is_bool($val)) {
            return $val;
        }
        if ($val === null) {
            return false;
        }
        $lower = strtolower((string) $val);
        return in_array($lower, ['t', 'true', '1', 'yes'], true);
    }
}