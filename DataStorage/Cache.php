<?php namespace PetrKnap\Utils\DataStorage;

/**
 * Simple PHP class for caching your temporally static content
 *
 * This class saves data to APC if can, or to database trough Database object.
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2014-07-08
 * @category DataStorage
 * @package  PetrKnap\Utils\DataStorage
 * @version  0.2
 * @license  https://github.com/petrknap/utils/blob/master/LICENSE MIT
 * @homepage http://dev.petrknap.cz/DataStorage/Cache.php.html
 * @example  CacheTest.php Test cases
 * @property string Prefix The cache prefix
 * @property bool DebugMode Debug mode flag
 *
 * @change 0.2 Added support for table prefix
 */
class Cache
{
    /**
     * @var bool Can use APC?
     */
    private $useAPC = false;

    /**
     * @var null|Database Alternative cache storage
     */
    private $dbCache = null;

    /**
     * @var string Prefix for cache table
     */
    private $dbCacheTablePrefix = "";

    /**
     * @var string Prefix for this cache
     */
    private $prefix = "";

    /**
     * @var bool Is debug mode enabled?
     */
    private $debugMode = false;

    /**
     * SQL queries
     */
    const
        CREATE_TABLE = "
        CREATE TABLE IF NOT EXISTS %s (
            cache_key VARCHAR(200) UNIQUE NOT NULL,
            cache_var TEXT,
            cache_expire_at INTEGER
        );",
        INSERT = "
        INSERT INTO %s (cache_key, cache_var, cache_expire_at)
            VALUES (:cache_key, :cache_var, :cache_expire_at)",
        SELECT = "SELECT cache_var FROM %s WHERE cache_key = ?",
        DELETE = "DELETE FROM %s WHERE cache_key = ?",
        EXPIRE = "DELETE FROM %s WHERE cache_expire_at < ? AND cache_expire_at IS NOT NULL",
        CLEAN = "DELETE FROM %s AND cache_key LIKE ?";

    /**
     * Creates new instance
     *
     * @param Database $DBCache Alternative cache storage
     * @param string $DBCacheTablePrefix Prefix for cache table
     * @throws \Exception If couldn't connect to APC and $DBCache is null.
     */
    public function __construct($DBCache = null, $DBCacheTablePrefix = null)
    {
        $this->useAPC = (extension_loaded('apc') && ini_get('apc.enabled'));

        if ($DBCache !== null && !$this->useAPC) {
            $this->dbCache = $DBCache;
            if (!$this->dbCache->IsConnected) {
                $this->dbCache->Connect();
            }
            $this->dbCache->CreateQuery(
                sprintf(
                    self::CREATE_TABLE,
                    "{$this->dbCacheTablePrefix}cache"
                ),
                Database::TYPE_SQLite
            );
        } else if (!$this->useAPC) {
            throw new \Exception("Couldn't use APC.");
        }
    }

    /**
     * Closes alternative cache storage
     */
    public function __destructor()
    {
        if ($this->dbCache) {
            $this->dbCache->Close();
        }
    }

    /**
     * Caches a variable in the data store, only if it's not already stored
     *
     * After the ttl has passed, the stored variable will be expunged from the cache
     * (on the next request). If no ttl is supplied (or if the ttl is 0), the value
     * will persist until it is removed from the cache manually.
     *
     * @param string $key Store the variable using this name, keys are cache-unique
     * @param mixed $var The variable to store
     * @param int $ttl Time To Live; store var in the cache for ttl seconds
     * @return bool
     */
    public function add($key, $var, $ttl = 0)
    {
        $key = "{$this->prefix}{$key}";
        $return = false;
        if ($this->useAPC) {
            $return = \apc_add($key, $var, $ttl);
        } else {
            $this->dbCache->IWillBeCareful();
            $this->dbCache->Query(
                sprintf(
                    self::EXPIRE,
                    "{$this->dbCacheTablePrefix}cache"
                ),
                time()
            );
            try {
                $this->dbCache->Query(
                    sprintf(
                        self::INSERT,
                        "{$this->dbCacheTablePrefix}cache"
                    ),
                    array(
                        "cache_key" => $key,
                        "cache_var" => \serialize($var),
                        "cache_expire_at" => $ttl != 0 ? time() + $ttl : null
                    )
                );
                $return = true;
            } catch (\PDOException $ignored) {
            }
        }
        return $return;
    }

    /**
     * Fetch a stored variable from the cache
     *
     * @link http://php.net/manual/en/function.apc-fetch.php
     * @param string $key The key used to store the value.
     * @return mixed The stored variable on success; FALSE on failure.
     */
    public function get($key)
    {
        if ($this->debugMode) {
            return false;
        }
        $key = "{$this->prefix}{$key}";
        $result = false;
        if ($this->useAPC) {
            $result = \apc_fetch($key);
        } else {
            $this->dbCache->IWillBeCareful();
            $this->dbCache->Query(
                sprintf(
                    self::EXPIRE,
                    "{$this->dbCacheTablePrefix}cache"
                ),
                time()
            );
            try {
                $results = $this->dbCache->Query(
                    sprintf(
                        self::SELECT,
                        "{$this->dbCacheTablePrefix}cache"
                    ),
                    $key
                );
                $result = $this->dbCache->FetchArray($results, Database::FETCH_ASSOC);
                if ($result) $result = \unserialize($result["cache_var"]);
            } catch (\PDOException $ignored) {
            }
        }
        return $result;
    }

    /**
     * Removes a stored variable from the cache
     *
     * @param string $key The key used to store the value.
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function del($key)
    {
        $key = "{$this->prefix}{$key}";
        $return = false;
        if ($this->useAPC) {
            $return = \apc_delete($key);
        } else {
            $this->dbCache->IWillBeCareful();
            $this->dbCache->Query(
                sprintf(
                    self::EXPIRE,
                    "{$this->dbCacheTablePrefix}cache"
                ),
                time()
            );
            try {
                $this->dbCache->IWillBeCareful();
                $this->dbCache->Query(
                    sprintf(
                        self::DELETE,
                        "{$this->dbCacheTablePrefix}cache"
                    ),
                    $key
                );
                $return = true;
            } catch (\PDOException $ignored) {
            }
        }
        return $return;
    }

    /**
     * Clears the APC cache
     *
     * The user cache will be cleared.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function clear()
    {
        $return = false;
        if ($this->useAPC) {
            $return = \apc_clear_cache("user");
        } else {
            try {
                $this->dbCache->IWillBeCareful();
                $this->dbCache->Query(
                    sprintf(
                        self::CLEAN,
                        "{$this->dbCacheTablePrefix}cache"
                    ),
                    "{$this->prefix}%"
                );
                $return = true;
            } catch (\PDOException $ignored) {
            }
        }
        return $return;
    }

    public function __get($name)
    {
        switch ($name) {
            case "DebugMode":
                return $this->debugMode;
            case "Prefix":
                return $this->prefix;
            default:
                throw new \Exception("Variable $" . $name . " not found.");
        }
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case "DebugMode":
                $this->debugMode = $value;
                break;
            case "Prefix":
                $this->prefix = $value;
                break;
            default:
                throw new \Exception("Variable $" . $name . " not found.");
        }
    }
}
