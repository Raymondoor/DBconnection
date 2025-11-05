<?php namespace Raymondoor;

class DBconnection{
    private static $pdo = null;
    private static $lastSql = null;
    private static $dsn = 'sqlite:./database.db'; // Edit here accordingly e.g. 'mysql:host=localhost;dbname=test' 'sqlite:/dir/database.db'
    private static $user = ''; // Username for DB
    private static $pass = ''; // Password for DB

    const TYPE_SQLITE = 'sqlite';
    const TYPE_MYSQL  = 'mysql';
    const TYPE_PGSQL  = 'pgsql';
    const TYPE_UNKNOWN = 'unknown';

    public static function init(string $dsn = '', string $user = '', $pass = ''){
        if(self::$pdo === null){
            try{
                if(!empty($dsn)){
                    self::$dsn = $dsn;
                }
                if(!empty($user)){
                    self::$user = $user;
                }
                if(!empty($pass)){
                    self::$pass = $pass;
                }
                self::$pdo = new \PDO(self::$dsn,self::$user,self::$pass);
                self::setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                self::setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            }catch(\PDOException $e){
                throw new \Exception('DBconnection Failed: '.$e->getMessage());
            }
        }
        else{
            trigger_error("DBconnection is already initialized", E_USER_NOTICE);
        }
    }
    public static function reset(){
        self::$pdo = null;
    }
    
    private static function setAttribute(int $attribute, $value){
        if(self::$pdo !== null){
            return self::$pdo->setAttribute($attribute, $value);
        }
    }
    // use this for CREATE DROP statements
    protected static function exec($sql):int{ // int>=0 or Exception
        if(empty($sql)){
            throw new \TypeError('Empty SQL Provided');
        }
        if(!is_string($sql)){
            throw new \TypeError('SQL must be provided in String.');
        }
        return self::$pdo->exec($sql);
    }
    public static function run(string $sql, array $params = []){
        if(empty($sql)){
            throw new \TypeError('Empty SQL Provided');
        }
        if(!is_string($sql)){
            throw new \TypeError('SQL must be provided in String.');
        }
        try{
            $pdo = self::$pdo;
            $stmt = $pdo->prepare($sql);
            $pdo->beginTransaction();
            $stmt->execute($params);

            $result = strpos(trim($sql), 'SELECT') === 0 ? $stmt->fetchAll() : $stmt->rowCount();

            $pdo->commit();
            self::$lastSql = $sql;
            return $result;
        }catch (\Exception $e){
            if($pdo->inTransaction()){
                $pdo->rollBack();
            }
            throw new \Exception('Query failed: '.$e->getMessage().' SQL: '.$sql);
        }
    }

    public static function lastInsertId():int{
        return (int)self::$pdo->lastInsertId();
    }
    public static function lastQuery():string{
        return self::$lastSql;
    }
    public static function getType(): string{
        if(strpos(self::$dsn, 'sqlite:') === 0) return self::TYPE_SQLITE;
        if(strpos(self::$dsn, 'mysql:') === 0)  return self::TYPE_MYSQL;
        if(strpos(self::$dsn, 'pgsql:') === 0)  return self::TYPE_PGSQL;
        return self::TYPE_UNKNOWN;
    }
}