<?php

/**
 * Base Redis model
 */
class Unsee_Redis
{

    const EXP_HOUR = 3600;
    const EXP_DAY = 86400;
    const DB = 0;

    /**
     * @var int Id of previously used database
     */
    static $prevDb = 0;

    /**
     * @var Redis Redis object
     */
    private $redis;

    /**
     * @var string Key of Redis hash field
     */
    public $key;

    /**
     * Creates the Redis model
     * @param type $key
     */
    public function __construct($key = null)
    {
        $this->redis = Zend_Registry::get('Redis');
        $this->key = $key;
    }

    /**
     * Support isset() for Redis model object
     * @param string $key
     * @return true
     */
    public function __isset($key)
    {
        $this->selectDb();
        return $this->redis->hExists($this->key, $key);
    }

    /**
     * Support unset() for Redis model object
     * @param type $key
     * @return int
     */
    public function __unset($key)
    {
        $this->selectDb();
        return $this->redis->hDel($this->key, $key);
    }

    /**
     * Fetches the content defined by the key
     * @param string $hKey
     * @return string
     */
    public function __get($hKey)
    {
        $this->selectDb();
        return $this->redis->hGet($this->key, $hKey);
    }

    /**
     * Sets the value of the hash defined by the key
     * @param string $hKey
     * @param mixed $value
     * @return bool
     */
    public function __set($hKey, $value)
    {
        $this->selectDb();
        return $this->redis->hSet($this->key, $hKey, $value);
    }

    /**
     * Sets the current database id to operate on
     * @return boolean
     */
    private function selectDb()
    {
        if (self::$prevDb !== static::DB) {
            $this->redis->select(static::DB);
            self::$prevDb = static::DB;
        }

        return true;
    }

    /**
     * Returns true if the specified hash exists
     * @param string $key
     * @return bool
     */
    public function exists($key = null)
    {
        if (!$key) {
            $key = $this->key;
        }

        $this->selectDb();
        return $this->redis->hLen($this->key) > 0;
    }

    /**
     * Deletes the Redis hash
     * @return int
     */
    public function delete()
    {
        $this->selectDb();
        return $this->redis->delete($this->key);
    }

    /**
     * Returns array representation of the Redis hash
     * @return array
     */
    public function export()
    {
        $this->selectDb();
        return $this->redis->hGetAll($this->key);
    }

    /**
     * Increments the value of the hash field by the specified number
     * @param string $key
     * @param int $num
     * @return bool
     */
    public function increment($key, $num = 1)
    {
        $this->selectDb();
        return $this->redis->hIncrBy($this->key, $key, $num);
    }

    public function expireAt($time)
    {
        $this->selectDb();
        return $this->redis->expireAt($this->key, $time);
    }

    public function ttl()
    {
        $this->selectDb();
        return $this->redis->ttl($this->key);
    }

    public static function keys($keys)
    {
        $redis = Zend_Registry::get('Redis');
        $redis->select(static::DB);
        self::$prevDb = static::DB;

        return $redis->keys($keys);
    }
}
