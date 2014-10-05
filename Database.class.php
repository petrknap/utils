<?php namespace PetrKnap\Utils\DataStorage;
/**
 * Simple PHP class for better work with SQL based databases
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2013-06-03
 * @category DataStorage
 * @package  PetrKnap\Utils\DataStorage
 * @version  2.2.3
 * @license  http://opensource.org/licenses/ms-pl.html MS-PL
 * @homepage http://dev.petrknap.cz/Database.class.php.html
 * @example  Database.example.php Basic usage example
 *
 * @property \PDO PhpDataObject Instance of PHP Data Object
 * @property int Type Type of your database - This value must be one of the TYPE_* constants, defaults to Type_MySQL
 * @property \Exception[] Warnings Array of exceptions throw at background
 * @property string HostOrPath The server address - It can also include a port number. e.g. "hostname:port" or a path to a local file e.g. "/path/to/db.file"
 * @property string DBName The name of your database
 * @property string Username The username
 * @property string Password  The password
 * @property string CharacterSet The encoding of your database
 * @property bool AmICareful Are you careful?
 *
 * @change 2.2.3 Moved to `PetrKnap\Utils\DataStorage`
 * @change 2.2.3 Added property `DBName`:[#property_DBName]
 * @change 2.2.2 Fully translated PhpDocs
 */
class Database
{
    /**
     * @var \PDO
     */
    private $phpDataObject = null;
    private $type = null;
    private $warnings = array();
    private $hostOrPath = null;
    private $dbName = null;
    private $username = null;
    private $password = null;
    private $characterSet = null;
    private $amICareful = false;

    /**
     * Supported databases
     */
    const
        TYPE_SQLite = 1,
        TYPE_MySQL = 2;
    /**
     * Supported fetch outputs
     */
    const
        FETCH_BOTH = \PDO::FETCH_BOTH,
        FETCH_ASSOC = \PDO::FETCH_ASSOC,
        FETCH_NUM = \PDO::FETCH_NUM;

    /**
     * Create a new blank instance
     */
    public function __construct()
    {
    }

    /**
     * Close connection (if necessary) and then destroy this object
     */
    public function __destruct()
    {
        if ($this->phpDataObject) $this->Close();
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        switch ($name) {
            case "PhpDataObject":
                return $this->phpDataObject;
            case "Type":
                return $this->type;
            case "Warnings":
                return $this->warnings;
            case "HostOrPath":
                return $this->hostOrPath;
            case "Username":
                return $this->username;
            case "DBName":
                return $this->dbName;
            case "CharacterSet":
                return $this->characterSet;
            case "AmICareful":
                return $this->amICareful;
            case "Password":
                throw new \Exception("Variable $" . $name . " is private.");
                break;
            default:
                throw new \Exception("Variable $" . $name . " not found.");
                break;
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case "PhpDataObject":
                $this->roSet("phpDataObject", $value);
                break;
            case "Type":
                $this->roSet("type", $value);
                break;
            case "Warnings":
                $this->roSet("warnings", $value);
                break;
            case "HostOrPath":
                $this->roSet("hostOrPath", $value);
                break;
            case "Username":
                $this->roSet("username", $value);
                break;
            case "Password":
                $this->roSet("password", $value);
                break;
            case "DBName":
                $this->roSet("dbName", $value);
                break;
            case "CharacterSet":
                $this->roSet("characterSet", $value);
                break;
            case "AmICareful":
                $this->roSet("amICareful", $value);
                break;
            default:
                throw new \Exception("Variable $" . $name . " not found.");
                break;
        }
    }

    /**
     * Setter for readonly variables
     *
     * @param string $name The name of attribute
     * @param mixed $value The value of attribute
     * @throws \Exception
     */
    private function roSet($name, $value)
    {
        if ($this->{$name} != null)
            throw new \Exception("Variable $" . $name . " is readonly (value " . $this->{$name} . ").");
        else $this->{$name} = $value;
    }

    /**
     * Opens a new connection to a database
     *
     * @param string $dbName The name of your database
     * @throws \Exception
     */
    public function Connect($dbName = null)
    {
        if ($this->phpDataObject) throw new \Exception("Only one connection to database per object is allowed.");
        if ($dbName !== null) $this->dbName = $dbName;
        switch ($this->type) {
            case self::TYPE_SQLite:
                $dsn = "sqlite:" . $this->hostOrPath;
                break;
            case self::TYPE_MySQL:
                if (empty($this->dbName))
                    throw new \Exception("Unspecified database name.");
                $dsn = "mysql:dbname=" . $this->dbName . ";host=" . $this->hostOrPath;
                break;
            default:
                throw new \Exception("Unsupported database type.");
                break;
        }
        try {
            $this->phpDataObject = new \PDO($dsn, $this->username, $this->password);
            $this->phpDataObject->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new \Exception("Could not connect: " . $e->getMessage());
        }
        if ($this->characterSet) try {
            self::Query("SET CHARACTER SET ?", $this->characterSet);
        } catch (\Exception $e) {
            $this->warnings[] = $e;
        }
    }

    /**
     * Disables fail-safe mechanism for next query
     */
    public function IWillBeCareful()
    {
        $this->amICareful = true;
    }

    /**
     * Sends a unique query (multiple queries are not supported) to the database
     *
     * You must call IWillBeCareful() method before send delete, alter or drop query.
     *
     * @param string $query This must be a valid SQL statement for the target database server
     * @param mixed $params An array of values with as many elements as there are bound parameters in the SQL statement or single value
     * @throws \Exception
     * @return \PDOStatement
     */
    public function Query($query, $params = array())
    {
        if (!$this->amICareful) {
            if (preg_match("/^\\s*(delete|alter|drop) /i", $query)) {
                throw new \Exception("You must be careful - call IWillBeCareful() method.");
            }
        }
        $this->amICareful = false;
        $result = $this->phpDataObject->prepare($query);
        if (!is_array($params)) $params = array($params);
        $result->execute($params);
        return $result;
    }

    /**
     * Returns an array that corresponds to the fetched row and moves the internal data pointer ahead
     *
     * @param \PDOStatement $result The result resource that is being evaluated. This result comes from a call to self::Query().
     * @param int $type             Controls how the next row will be returned to the caller - This value must be one of the Database::FETCH_* constants, defaults to Database::FETCH_BOTH
     * @return array
     */
    public function FetchArray($result, $type = self::FETCH_BOTH)
    {
        return $result->fetch($type);
    }

    /**
     * Closes the connection to the database
     */
    public function Close()
    {
        $this->phpDataObject = null;
    }
}

#region Backward compatibility
namespace PetrKnap\IndependentClass;

class Database extends \PetrKnap\Utils\DataStorage\Database {}
#endregion