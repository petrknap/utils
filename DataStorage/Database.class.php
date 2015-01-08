<?php namespace PetrKnap\Utils\DataStorage;
/**
 * Simple PHP class for better work with SQL based databases
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2013-06-03
 * @category DataStorage
 * @package  PetrKnap\Utils\DataStorage
 * @version  2.3.0
 * @license  https://github.com/petrknap/utils/blob/master/LICENSE MIT
 * @homepage http://dev.petrknap.cz/Database.class.php.html
 * @example  Database.example.php Basic usage example
 *
 * @property bool IsConnected True if connection to database is active, otherwise false.
 * @property \PDO PhpDataObject Instance of PHP Data Object
 * @property int Type Type of your database - This value must be one of the TYPE_* constants, defaults to Type_MySQL
 * @property \Exception[] Warnings Array of exceptions throw at background
 * @property string HostOrPath The server address - It can also include a port number. e.g. "hostname:port" or a path to a local file e.g. "/path/to/db.file"
 * @property string DBName The name of your database
 * @property string Username The username
 * @property string Password  The password
 * @property string CharacterSet The encoding of your database
 * @property bool AmICareful Are you careful?
 * @property string LastInsertId The ID of the last inserted row or sequence value
 *
 * @change 2.3.1 Added property `IsConnected`:[#property_IsConnected]
 * @change 2.3.0 Used `DatabaseException` instead of `\Exception`
 * @change 2.3.0 Added method `BeginTransaction`["#method_BeginTransaction"]
 * @change 2.3.0 Added method `Commit`["#method_Commit"]
 * @change 2.3.0 Added method `RollBack`["#method_RollBack"]
 * @change 2.3.0 Fixed minority error use cases
 * @change 2.2.4 Added method `lastInsertId`:[#method_lastInsertId]
 * @change 2.2.4 Added property `LastInsertId`:[#property_LastInsertId]
 * @change 2.2.3 Changed licensing from "MS-PL":[http://opensource.org/licenses/ms-pl.html] to "MIT":[https://github.com/petrknap/utils/blob/master/LICENSE]
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
    private $hasActiveTransaction = false;

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
     * @throws DatabaseException
     */
    public function __get($name)
    {
        switch ($name) {
            case "IsConnected":
                return $this->phpDataObject !== null;
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
            case "LastInsertId":
                return $this->lastInsertId();
            case "Password":
                throw new DatabaseException("Variable $" . $name . " is private.", DatabaseException::SecurityException);
                break;
            default:
                throw new DatabaseException("Variable $" . $name . " not found.");
                break;
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws DatabaseException
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
            case "LastInsertId":
                throw new DatabaseException("Variable $" . $name . " is readonly.");
                break;
            default:
                throw new DatabaseException("Variable $" . $name . " not found.");
                break;
        }
    }

    /**
     * Setter for readonly variables
     *
     * @param string $name The name of attribute
     * @param mixed $value The value of attribute
     * @throws DatabaseException
     */
    private function roSet($name, $value)
    {
        if ($this->{$name} != null) {
            try {
                throw new DatabaseException("Variable $" . $name . " is readonly (value " . serialize($this->{$name}) . ").");
            } catch (\Exception $e) {
                throw new DatabaseException("Variable $" . $name . " is readonly.");
            }
        } else {
            $this->{$name} = $value;
        }
    }

    /**
     * @return \PDO Instance of PHP Data Object
     * @throws DatabaseException
     */
    private function getPDO()
    {
        if (!$this->phpDataObject) {
            throw new DatabaseException("Database is not connected.", DatabaseException::AccessException);
        }
        return $this->phpDataObject;
    }

    /**
     * Opens a new connection to a database
     *
     * @param string $dbName The name of your database
     * @return $this
     * @throws DatabaseException
     */
    public function Connect($dbName = null)
    {
        if ($this->phpDataObject) {
            throw new DatabaseException("Only one connection to database per object is allowed.", DatabaseException::AccessException);
        }
        if ($dbName !== null) $this->dbName = $dbName;
        switch ($this->type) {
            case self::TYPE_SQLite:
                $dsn = "sqlite:" . $this->hostOrPath;
                break;
            case self::TYPE_MySQL:
                if (empty($this->dbName))
                    throw new DatabaseException("Unspecified database name.");
                $dsn = "mysql:dbname=" . $this->dbName . ";host=" . $this->hostOrPath;
                break;
            default:
                throw new DatabaseException("Unsupported database type.", DatabaseException::AccessException);
                break;
        }
        if (empty($this->hostOrPath)) {
            throw new DatabaseException("Unspecified database host/file.", DatabaseException::AccessException);
        }
        try {
            $this->phpDataObject = new \PDO($dsn, $this->username, $this->password);
            $this->phpDataObject->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new DatabaseException("Could not connect: " . $e->getMessage(), DatabaseException::AccessException, $e);
        }
        if ($this->characterSet) try {
            $this->Query("SET CHARACTER SET ?", $this->characterSet);
        } catch (\Exception $e) {
            $this->warnings[] = $e;
        }

        return $this;
    }

    /**
     * Disables fail-safe mechanism for next query
     *
     * @return $this
     */
    public function IWillBeCareful()
    {
        $this->amICareful = true;

        return $this;
    }

    /**
     * Returns the ID of the last inserted row or sequence value
     *
     * @param string $name Name of the sequence object from which the ID should be returned
     * @return string Returns a string representing the row ID of the last row that was inserted into the database
     * @throws DatabaseException
     */
    public function lastInsertId($name = null)
    {
        return $this->getPDO()->lastInsertId($name);
    }

    /**
     * Sends a unique query (multiple queries are not supported) to the database
     *
     * You must call IWillBeCareful() method before send delete, alter or drop query.
     *
     * @param string $query This must be a valid SQL statement for the target database server
     * @param mixed $params An array of values with as many elements as there are bound parameters in the SQL statement or single value
     * @return \PDOStatement
     * @throws DatabaseException
     */
    public function Query($query, $params = array())
    {
        if (!$this->amICareful) {
            if (preg_match("/^\\s*(delete|alter|drop) /i", $query)) {
                throw new DatabaseException("You must be careful - call IWillBeCareful() method.", DatabaseException::SecurityException);
            }
        }
        $this->amICareful = false;

        $statement = $this->getPDO()->prepare($query);
        if (!is_array($params)) $params = array($params);
        $statement->execute($params);

        return $statement;
    }

    /**
     * Checks if instance has active transaction
     *
     * @param bool $value
     * @throws DatabaseException
     */
    private function checkTransaction($value)
    {
        if ($this->hasActiveTransaction != $value) {
            $message = "";
            switch ($value) {
                case true:
                    $message = "You haven't active transaction - call BeginTransaction() method.";
                    break;
                case false:
                    $message = "You have active transaction - call Commit() or RollBack() method.";
                    break;

            }
            throw new DatabaseException($message, DatabaseException::AccessException);
        }
    }

    /**
     * Begins new transaction
     *
     * @return $this
     * @throws DatabaseException
     */
    public function BeginTransaction()
    {
        $this->checkTransaction(false);
        if (!$this->getPDO()->beginTransaction()) {
            throw new DatabaseException("Couldn't begin transaction", DatabaseException::AccessException);
        }
        $this->hasActiveTransaction = true;

        return $this;
    }

    /**
     * Commits current transaction
     *
     * @return $this
     * @throws DatabaseException
     */
    public function Commit()
    {
        $this->checkTransaction(true);
        if (!$this->getPDO()->commit()) {
            throw new DatabaseException("Couldn't commit transaction", DatabaseException::AccessException);
        }
        $this->hasActiveTransaction = false;

        return $this;
    }

    /**
     * Roll backs current transaction
     *
     * @return $this
     * @throws DatabaseException
     */
    public function RollBack()
    {
        $this->checkTransaction(true);
        if (!$this->getPDO()->rollBack()) {
            throw new DatabaseException("Couldn't rollback transaction", DatabaseException::AccessException);
        }
        $this->hasActiveTransaction = false;

        return $this;
    }

    /**
     * Returns an array that corresponds to the fetched row and moves the internal data pointer ahead
     *
     * @param \PDOStatement $statement The statement resource that is being evaluated. This statement comes from a call to Query().
     * @param int $type Controls how the next row will be returned to the caller - This value must be one of the FETCH_* constants, defaults to FETCH_BOTH
     * @return array
     */
    public function FetchArray($statement, $type = self::FETCH_BOTH)
    {
        return $statement->fetch($type);
    }

    /**
     * Closes the connection to the database
     *
     * @return $this
     * @throws DatabaseException
     */
    public function Close()
    {
        $this->checkTransaction(false);
        $this->phpDataObject = null;

        return $this;
    }
}

class DatabaseException extends \Exception
{
    const
        GenericException = 0,
        AccessException = 1,
        SecurityException = 2,
        PDOException = 3;
}

#region Backward compatibility
namespace PetrKnap\IndependentClass;

class Database extends \PetrKnap\Utils\DataStorage\Database {}
#endregion