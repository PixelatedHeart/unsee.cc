<?php

class Unsee_Redis
{

    private $redis;
    public $key;
    protected $db = 0;

    public function __construct($key = null)
    {
        $this->redis = Zend_Registry::get('Redis');
        $this->key = $key;
    }

    public function __get($hKey)
    {
        $this->redis->select($this->db);
        return $this->redis->hGet($this->key, $hKey);
    }

    public function __set($hKey, $value)
    {
        $this->redis->select($this->db);
        return $this->redis->hSet($this->key, $hKey, $value);
    }

    public function exists()
    {
        $this->redis->select($this->db);
        return $this->redis->hLen($this->key) > 0;
    }

    public function delete()
    {
        $this->redis->select($this->db);
        return $this->redis->delete($this->key);
    }

    public function export()
    {
        $this->redis->select($this->db);
        return $this->redis->hGetAll($this->key);
    }

    public function increment($key, $num = 1)
    {
        $this->redis->select($this->db);
        return $this->redis->hGetAll($this->key, $key, $num);
    }
}
