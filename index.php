<?php
include "app/Connection.php";

$db = Connection::get();

if (!$db) {
    die();
}

// Hужно вызвать один раз для создания и заполнения таблицы.
// При дублирующих вызовах не сломается, но будет делать бессмысленную работу
$res = initTable($db);
if (!$res) {
    die();
}

// Весь список.
$res = showBicycles($db, 1);
if (!$res) {
    die();
}
echo "\n";

// Родитель - Горные.
$res = showBicycles($db, 1, 2);
if (!$res) {
    die();
}

Connection::closeConnectDB();

function initTable($db)
{
    try {
        $query = pg_query($db, "CREATE TABLE IF NOT EXISTS
            bicycles (
            id         SERIAL        PRIMARY KEY,
            name       TEXT,
            left_key   INTEGER       NOT NULL,
            right_key  INTEGER       NOT NULL,
            level      INTEGER       NOT NULL DEFAULT 0
            );
            
            CREATE UNIQUE INDEX IF NOT EXISTS idx_bicycles_key_left_key_right_key_level
                ON bicycles (left_key, right_key, level);
                
            INSERT INTO bicycles (name, left_key, right_key, level) VALUES
                ('Bicycles', 1, 16, 0),
                ('Mountain', 2, 9, 1),
                ('Cross Country', 3, 4, 2),
                ('Downhill', 5, 6, 2),
                ('Trail', 7, 8, 2),
                ('Road', 10, 15, 1),
                ('Classic', 11, 12, 2),
                ('Gravel', 13, 14, 2)
            ON CONFLICT DO NOTHING;");

    } catch (PDOException $e) {
        echo "Ошибка создания таблицы: " . $e->getMessage();
        return false;
    }
    return true;
}

// $outputType: 0 - вывод списком, 1 - вывод деревом
function showBicycles($db, $outputType = 0, $parentID = -1)
{
    if ($outputType > 1) { 
        echo "Некорректный тип вывода, будет показан список.";
        $outputType = 0;
    }
   
    $queryStr = "";
    if ($parentID > -1) {
        $queryStr = "SELECT node.name, node.level, node.id
            FROM bicycles AS node, bicycles AS parent
            WHERE node.left_key BETWEEN parent.left_key AND parent.right_key AND parent.id = $1";
    } else {
        $queryStr = "SELECT node.name, node.level, node.id FROM bicycles AS node"; 
    }

    if ($outputType == 1) { 
        $queryStr = $queryStr . " ORDER BY node.left_key";

    }
    $queryStr =  $queryStr . ";";

    if ($parentID > -1) {
        try {
            $query = pg_query_params($db, $queryStr, array($parentID));
        } catch (PDOException $e) {
            echo "Ошибка выполнения запроса показа данных таблицы: " . $e->getMessage();
            return false;
        }
    } else {
        try {
            $query = pg_query($db, $queryStr);
        } catch (PDOException $e) {
            echo "Ошибка выполнения запроса показа данных таблицы: " . $e->getMessage();
            return false;
        }
    }

    while ($result = pg_fetch_array($query)) {
        echo $result['name'] . ' ' . $result['level'] . ' '
            . $result['id'] . "\n";
    }
    return true;
}

?>
