<?php

require_once("./Database.class.php");

use PetrKnap\Utils\DataStorage\Database;

header("Content-Type: text/plain; charset=utf-8");

// Define and config database object
$db = new Database();
$db->Type = $db::TYPE_SQLite;
$db->HostOrPath = ":memory:";
$db->CharacterSet = "UTF8";

// Connect to database
$db->Connect();


// Create new table
$db->Query("CREATE TABLE IF NOT EXISTS MyTable (" .
    "id INTEGER PRIMARY KEY," .
    "data TEXT" .
");");

// Fill up table
$db->Query("INSERT INTO MyTable (data) VALUES ('data1')");
$db->Query("INSERT INTO MyTable (data) VALUES (?)", "data2");
$db->Query("INSERT INTO MyTable (data) VALUES (?)", array("data3"));
$db->Query("INSERT INTO MyTable (data) VALUES (:data)", array("data" => "data4"));


// Select end print data from table
$results = $db->Query("SELECT * FROM MyTable");
print("Table rows:\n");
while ($result = $db->FetchArray($results, $db::FETCH_ASSOC)) {
    printf("\tID: %u, Data: %s\n", $result["id"], $result["data"]);
}
print("\n");


// Am I careful?
print(($db->AmICareful ? "I am careful." : "I am not careful.") . "\n"); // No.
$db->IWillBeCareful();
print(($db->AmICareful ? "I am careful." : "I am not careful.") . "\n"); // Yes.
$db->Query("DELETE FROM MyTable WHERE data = ?", "data2");
print(($db->AmICareful ? "I am careful." : "I am not careful.") . "\n"); // No.
print("\n");

// Select end print data from table
$results = $db->Query("SELECT * FROM MyTable");
print("Table rows:\n");
while ($result = $db->FetchArray($results, $db::FETCH_ASSOC)) {
    printf("\tID: %u, Data: %s\n", $result["id"], $result["data"]);
}
print("\n");

// I try drop table, but I am not careful -> I catch exception
try {
    $db->Query("DROP TABLE MyTable");
} catch (Exception $e) {
    print($e->getMessage()."\n");
}

// I promise that I will be careful and then I try drop table
$db->IWillBeCareful();
$db->Query("DROP TABLE MyTable");

// Disconnect from database
$db->Close(); // or unset($db);

/* Output:

 Table rows:
 	ID: 1, Data: data1
 	ID: 2, Data: data2
 	ID: 3, Data: data3
 	ID: 4, Data: data4

 I am not careful.
 I am careful.
 I am not careful.

 Table rows:
 	ID: 1, Data: data1
 	ID: 3, Data: data3
 	ID: 4, Data: data4

 You must be careful - call IWillBeCareful() method.

*/