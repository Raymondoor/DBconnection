<?php namespace Raymondoor;

class DBoperation extends DBconnection{
    public static function makeTableIfNot(string $table, string $schema){
        if(empty($table) || empty($schema)){
            throw new \Exception('Table name and schema cannot be empty.');
        }
        return static::exec("CREATE TABLE IF NOT EXISTS {$table} ({$schema})");
    }
    public static function dropTableIfIs(string $table){
        try{
            $statement = 'DROP TABLE IF EXISTS '.$table;
            return static::exec($statement);
        }catch(\Exception $e){
            return array('No such table');
        }
    }
    public static function allFrom(string $table){
        try{
            $statement = 'SELECT * FROM '.$table.' ORDER BY id ASC';
            return static::run($statement);
        }catch(\Exception $e){
            return array('error' => $e->getMessage());
        }
    }
    public static function fetchOne(string $stmt, array $param=[]){
        try{
            if(strpos(trim($stmt), 'SELECT') !== 0){
                throw new \Exception('fetchOne() must be used for SELECT only');
            }
            return static::run($stmt, $param)[0];
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }
    // useful column presets. make sure to add ',' before/after calling these methods if needed.
    public static function create_id(){
        switch(static::getType()){
            case static::TYPE_SQLITE:
                return 'id INTEGER PRIMARY KEY AUTOINCREMENT';
                break;
            case static::TYPE_MYSQL:
                return 'id INT AUTO_INCREMENT PRIMARY KEY';
                break;
            case static::TYPE_PGSQL:
                return 'id SERIAL PRIMARY KEY';
                break;
        }
    }
    public static function create_time_record(){
        switch(static::getType()){
            case static::TYPE_SQLITE:
                return 'created_at DATETIME DEFAULT (DATETIME(\'now\', \'localtime\'))';
                break;
            case static::TYPE_MYSQL:
                return 'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP';
                break;
            case static::TYPE_PGSQL:
                return 'created_at TIMESTAMP DEFAULT NOW()';
                break;
        }
    }
}