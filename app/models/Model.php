<?php
namespace App\Models;

class Model 
{
    protected static $pdo = null;
    protected $table;
    protected $attributes = [];
    protected $isNew = true;

    public function __construct()
    {
        if (self::$pdo === null) {
            $host = getenv('DB_HOST');
            $dbname = getenv('DB_NAME');
            $username = getenv('DB_USER');
            $password = getenv('DB_PASS');

            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
            try {
                self::$pdo = new \PDO($dsn, $username, $password);
                self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
    }

    public function create($data)
    {
        $fields = implode(',', array_keys($data));
        $placeholders = ':' . implode(',:', array_keys($data));
        $sql = "INSERT INTO $this->table ($fields) VALUES ($placeholders)";
        $stmt = self::$pdo->prepare($sql);

        $result = $stmt->execute($data);

        if ($result) {
            $this->attributes = $data;
            $this->attributes['id'] = self::$pdo->lastInsertId();
            $this->isNew = false;
            return $this;
        }

        return $result;
    }

    public function update($id, $data)
    {
        $fields = '';

        foreach ($data as $key => $value) {
            $fields .= "$key = :$key, ";
        }

        $fields = rtrim($fields, ', ');
        $sql = "UPDATE $this->table SET $fields WHERE id = :id";
        $stmt = self::$pdo->prepare($sql);
        $data['id'] = $id;

        return $stmt->execute($data);
    }

    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function save()
    {
        if ($this->isNew) {
            return $this->create($this->attributes);
        } else {
            return $this->update($this->attributes['id'], $this->attributes);
        }
    }
}
