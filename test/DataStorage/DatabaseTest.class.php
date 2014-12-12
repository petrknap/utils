<?php

use PetrKnap\Utils\DataStorage\Database;
use PetrKnap\Utils\DataStorage\DatabaseException;

require_once(__DIR__ . "/../../DataStorage/Database.class.php");

class DatabaseTest extends PHPUnit_Framework_TestCase
{
    #region Empty tests
    public function test_emptyType_connect()
    {
        try {
            $db = new Database();
            $db->Connect();
            $this->fail("Empty type doesn't throw exception.");
        } catch (DatabaseException $de) {
            $this->assertEquals(DatabaseException::AccessException, $de->getCode());
        }

    }

    public function test_emptyHostOrFile_connect()
    {
        try {
            $db = new Database();
            $db->Type = Database::TYPE_SQLite;
            $db->Connect();
            $this->fail("Empty hostOrFile doesn't throw exception.");
        } catch (DatabaseException $de) {
            $this->assertEquals(DatabaseException::AccessException, $de->getCode());
        }
    }

    public function test_emptyPhpDataObject_query()
    {
        try {
            $db = new Database();
            $db->Query("");
            $this->fail("Query on empty PhpDataObject doesn't throw exception.");
        } catch (DatabaseException $de) {
            $this->assertEquals(DatabaseException::AccessException, $de->getCode());
        }
    }

    public function test_emptyPhpDataObject_lastInsertId()
    {
        try {
            $db = new Database();
            $db->lastInsertId();
            $this->fail("lastInsertId on empty PhpDataObject doesn't throw exception.");
        } catch (DatabaseException $de) {
            $this->assertEquals(DatabaseException::AccessException, $de->getCode());
        }
    }
    #endregion

    #region Fail-safe mechanism tests
    public function test_failSave_doubleConnection()
    {
        $db = new Database();
        $db->Type = Database::TYPE_SQLite;
        $db->HostOrPath = ":memory:";
        $db->Connect();
        try {
            $db->Connect();
            $this->fail("Second connection doesn't throw exception.");
        } catch (DatabaseException $de) {
            $this->assertEquals(DatabaseException::AccessException, $de->getCode());
        }
        $db->Close();
    }

    public function test_failSave_roSet()
    {
        $roVars = array(
            "PhpDataObject",
            "Type",
            "Warnings",
            "HostOrPath",
            "Username",
            "Password",
            "DBName",
            "CharacterSet",
            "AmICareful",
            "LastInsertId",
            "InnerStatement"
        );
        $db = new Database();
        $db->Type = Database::TYPE_SQLite;
        $db->HostOrPath = ":memory:";
        $db->Connect();
        foreach ($roVars as $roVar) {
            try {
                if (!$db->{$roVar}) {
                    $db->{$roVar} = true;
                }
            } catch (DatabaseException $de) {
                if($de->getCode() == DatabaseException::SecurityException) {
                    continue;
                }
            }
            try {
                $db->{$roVar} = false;
                $this->fail("{$roVar} = false doesn't throw exception.");
            } catch (DatabaseException $de) {
                $this->assertEquals(DatabaseException::GenericException, $de->getCode());
            }
        }
    }

    public function test_failSave_doubleTransaction()
    {
        $db = new Database();
        $db->Type = Database::TYPE_SQLite;
        $db->HostOrPath = ":memory:";
        $db->Connect()->BeginTransaction();
        try {
            $db->BeginTransaction();
            $this->fail("Second transaction doesn't throw exception.");
        } catch(DatabaseException $de) {
            $this->assertEquals(DatabaseException::AccessException, $de->getCode());
        }
        $db->RollBack()->Close();
    }

    public function test_failSave_commitWithoutTransaction()
    {
        $db = new Database();
        $db->Type = Database::TYPE_SQLite;
        $db->HostOrPath = ":memory:";
        $db->Connect();
        try {
            $db->Commit();
            $this->fail("Commit without transaction doesn't throw exception.");
        } catch(DatabaseException $de) {
            $this->assertEquals(DatabaseException::AccessException, $de->getCode());
        }
        $db->Close();
    }

    public function test_failSave_rollBackWithoutTransaction()
    {
        $db = new Database();
        $db->Type = Database::TYPE_SQLite;
        $db->HostOrPath = ":memory:";
        $db->Connect();
        try {
            $db->RollBack();
            $this->fail("Roll back without transaction doesn't throw exception.");
        } catch(DatabaseException $de) {
            $this->assertEquals(DatabaseException::AccessException, $de->getCode());
        }
        $db->Close();
    }

    public function test_failSave_closeWithTransaction()
    {
        $db = new Database();
        $db->Type = Database::TYPE_SQLite;
        $db->HostOrPath = ":memory:";
        $db->Connect()->BeginTransaction();
        try {
            $db->Close();
            $this->fail("Close with transaction doesn't throw exception.");
        } catch(DatabaseException $de) {
            $this->assertEquals(DatabaseException::AccessException, $de->getCode());
        }
        $db->RollBack()->Close();
    }

    public function test_failSave_dangerQuery()
    {
        $queries = array(
            "DELETE FROM table_name",
            "ALTER TABLE table_name ADD column_name INTEGER",
            "DROP TABLE table_name"
        );
        $db = new Database();
        $db->Type = Database::TYPE_SQLite;
        $db->HostOrPath = ":memory:";
        $db->Connect();
        $db->Query("CREATE TABLE IF NOT EXISTS table_name (id INTEGER PRIMARY KEY, data TEXT)");
        foreach ($queries as $query) {
            try {
                $db->Query($query);
                $this->fail("Danger query doesn't throw exception.");
            } catch (DatabaseException $de) {
                $this->assertEquals(DatabaseException::SecurityException, $de->getCode());
            }
        }
        foreach ($queries as $query) {
            $this->assertFalse($db->AmICareful);
            $db->IWillBeCareful();
            $this->assertTrue($db->AmICareful);
            $db->Query($query);
            $this->assertFalse($db->AmICareful);
        }
        $db->Close();
    }
    #endregion

    #region Performance tests
    public function test_performance()
    {
        $db = new Database();
        $db->Type = Database::TYPE_SQLite;
        $db->HostOrPath = ":memory:";
        $db->Connect();
        for ($i = 0; $i < 25; $i++) {

            // Create table and fill it
            $db->Query(
                "CREATE TABLE IF NOT EXISTS table_{$i} (id INTEGER PRIMARY KEY, content TEXT)"
            );
            $k = 1;
            $step = 500;
            $stop = 100000;
            while ($k < $stop) {
                $keys = [];
                $values = [];
                for ($j = 0; $j < $step; $j++) {
                    $keys[] = "(:d{$j})";
                    $values["d{$j}"] = "d{$j}";
                }
                $db->Query(
                    "INSERT INTO table_{$i} (content) VALUES " . join(", ", $keys),
                    $keys
                );
                $k += $step;
                $this->assertEquals($k - 1, $db->LastInsertId);
            }

            // Select many IDs, remove records by IDs and roll back changes
            $db->BeginTransaction();
            $rs = $db->Query("SELECT id FROM table_{$i} WHERE (id > 50 AND id < 101) OR (id > 250 AND id < 451)");
            $ids = [];
            while ($r = $db->FetchArray($rs)) {
                $ids[] = $r["id"];
            }
            $db->IWillBeCareful();
            $db->Query(
                "DELETE FROM table_{$i} WHERE id IN (:ids)",
                array(
                    "ids" => join(", ", $ids)
                )
            );
            $db->RollBack();

            // Update records by content and commit changes
            $db->BeginTransaction();
            $db->Query(
                "UPDATE table_{$i} SET content = 'd1 or d3' WHERE content LIKE :d1 OR content LIKE :d3",
                array(
                    "d1" => "d%1",
                    "d3" => "d%3"
                )
            );
            $db->Commit();

        }
        $db->Close();
    }
    #endregion
}