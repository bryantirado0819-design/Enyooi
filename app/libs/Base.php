<?php
/*
 * Clase Base para la conexión a la base de datos utilizando PDO.
 * VERSIÓN CORREGIDA Y MEJORADA
 */
class Base
{
    protected $dbhost = DB_HOST;
    protected $dbname = DB_NAME;
    protected $dbuser = DB_USER;
    protected $dbpass = DB_PASSWORD;

    private $cnx;
    private $stmt;
    private $error;

    public function __construct()
    {
        $dbh = "mysql:host=" . $this->dbhost . ";dbname=" . $this->dbname;
        $options = [
            PDO::ATTR_PERSISTENT => true, // Conexiones persistentes
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ];

        try {
            $this->cnx = new PDO($dbh, $this->dbuser, $this->dbpass, $options);
            $this->cnx->exec("set names utf8mb4");
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            echo "Error de Conexión: " . $this->error;
        }
    }

    public function query($sql)
    {
        $this->stmt = $this->cnx->prepare($sql);
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

    public function execute()
    {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            echo "Error de Ejecución: " . $this->error;
            return false;
        }
    }

    public function registers()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }
    public function resultSet()
    {
        return $this->registers();
    }

    // MÉTODO getOne (versión renombrada)
    public function getOne()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }
    
    // ¡¡CORRECCIÓN!! Se reincorpora single() para compatibilidad
    public function single() {
        return $this->getOne();
    }
    
    // Se mantiene 'register' por compatibilidad
    public function register()
    {
        return $this->getOne();
    }

    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    public function lastInsertId()
    {
        return $this->cnx->lastInsertId();
    }

    // --- NUEVOS MÉTODOS PARA TRANSACCIONES SEGURAS ---

    /**
     * Inicia una nueva transacción.
     */
    public function beginTransaction()
    {
        return $this->cnx->beginTransaction();
    }

    /**
     * Confirma los cambios de la transacción actual.
     */
    public function commit()
    {
        return $this->cnx->commit();
    }

    /**
     * Revierte los cambios de la transacción actual.
     */
    public function rollBack()
    {
        return $this->cnx->rollBack();
    }
}
?>