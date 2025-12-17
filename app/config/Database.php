<?php
  class Database {
    private static $pdo = null;

    public static function getConnection() {
        if(self::$pdo === null) {
            $cfg = require __DIR__ . '/config.php';
            $db = $cfg['db'];
            $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            self::$pdo = new PDO($dsn, $db['user'], $db['pass'], $options);
        }
        return self::$pdo;
    }
}
