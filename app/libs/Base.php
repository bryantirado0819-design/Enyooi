<?php

class Base
{
    protected $dbhost = DB_HOST;
    protected $dbname = DB_NAME;
    protected $dbuser = DB_USER;
    protected $dbpass = DB_PASSWORD;

    private $cnx;
    private $stmt;
    private $error;
    private $dbh;

    public function __construct()
    {
        $dbh = "mysql:host=" . $this->dbhost . ";dbname=" . $this->dbname;

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ];

        try {
            $this->cnx = new PDO($dbh, $this->dbuser, $this->dbpass, $options);
            $this->cnx->exec("set names utf8");
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            echo $this->error;
        }
    }
public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }
    public function query($sql)
    {
        $this->stmt = $this->cnx->prepare($sql);
    }

    public function execute()
    {
        return $this->stmt->execute();
    }

    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
                    break;
            }
        }

        $this->stmt->bindValue($param, $value, $type);
    }

    public function register()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    public function registers()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function rowCount()
    {
        
        return $this->stmt->rowCount();  // Corregido el error tipográfico
    }
}
?>
