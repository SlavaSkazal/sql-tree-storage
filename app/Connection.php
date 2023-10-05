<?php
class Connection { 
    // Соединение статическое, остается возможность того, что одновременно в двух местах подключатся к соединению,
    // и если в одном месте его закроют, а в другом будут еще работать.
    // Как я понимаю, тут либо следить за этим, либо сделать класс не статическим, если к соединению часто обращаются,
    // либо может какой-то счетчик подлючений прикрутить, чтоб давало закрыть соединение
    // только после окончания работы всех к нему подключенных.
    private static $db_connect;
    const host = "127.0.0.1";
    const port = "5432";
    const dbname = "tree_db";
    const user = "own_user";
    const password = "1234";

    public static function get() {
        if (static::$db_connect === null) {
            static::$db_connect = static::connectDB();
        }
        return static::$db_connect;
    }

    private static function connectDB() {
        $connect_data = sprintf("host=%s port=%s dbname=%s user=%s password=%s", 
            self::host, 
            self::port, 
            self::dbname, 
            self::user, 
            self::password);

        try {
            static::$db_connect = pg_connect($connect_data);
        } catch (PDOException $e) {
            echo "Ошибка создания соединения: " . $e->getMessage();
            return false;
        }
        return static::$db_connect;
    }

    public static function closeConnectDB() {
        static::$db_connect = null;
    }      
}

?>
