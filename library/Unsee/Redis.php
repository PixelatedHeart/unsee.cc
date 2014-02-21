<?php

class Unsee_Redis
{

    static $prevDb = 0;
    private $redis;
    public $key;
    protected $db = 0;

    public function __construct($key = null)
    {
        $this->redis = Zend_Registry::get('Redis');
        $this->key = $key;
    }

    public function __isset($key)
    {
        $this->selectDb();
        return $this->redis->hExists($this->key, $key);
    }

    public function __get($hKey)
    {
        $this->selectDb();
        return $this->redis->hGet($this->key, $hKey);
    }

    public function __set($hKey, $value)
    {
        $this->selectDb();
        return $this->redis->hSet($this->key, $hKey, $value);
    }

    private function selectDb()
    {
        if (self::$prevDb !== $this->db) {
            $this->redis->select($this->db);
            self::$prevDb = $this->db;
        }

        return true;
    }

    public function exists($key = null)
    {
        if (!$key) {
            $key = $this->key;
        }

        $this->selectDb();
        return $this->redis->hLen($this->key) > 0;
    }

    public function delete()
    {
        $this->selectDb();
        return $this->redis->delete($this->key);
    }

    public function export()
    {
        $this->selectDb();
        return $this->redis->hGetAll($this->key);
    }

    public function increment($key, $num = 1)
    {
        $this->selectDb();
        return $this->redis->hGetAll($this->key, $key, $num);
    }
}
