<?php
class DBconnection{
    private static $dsn = 'sqlite:database.db'; // Edit here accordingly e.g. 'mysql:host=localhost;dbname=test' 'sqlite:/dir/database.db'
    private static $user = ''; // Username for DB
    private static $pass = ''; // Password for DB
    private static $errpath = __DIR__.'/error.log'; // Error Log Path

    private static $pdo = null;
    public static function connect(){
        if(self::$pdo === null){
            try{
                self::$pdo = new PDO(self::$dsn,self::$user,self::$pass);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            }catch(PDOException $e){
                $errmessage = 'DBconnection Failed: '.$e->getmessage();
                error_log($errmessage.PHP_EOL, 3, self::$errpath);
                throw new Exception($errmessage);
            }
            return self::$pdo;
        }
        return self::$pdo;
    }
}
class DBstatement{
    private $statement;
    private $pdo;
    public function __construct(string $statement){
        $this->statement = trim($statement);
        try{
            $this->pdo = DBconnection::connect();
        }catch(Exception $e){
            throw new Exception('Initialize Failed: '.$e->getMessage());
        }
    }
    public function execute(array $parameters = []){
        try{
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($this->statement);
            if(!$stmt){ // Just in case
                throw new Exception('Prepare Statement Failed: '.$this->statement);
            }
            $executed = $stmt->execute($parameters);
            if(!$executed){ // Just in case
                $this->pdo->rollBack();
                $errorInfo = $stmt->errorInfo();
                throw new Exception('Execution Failed: '.$errorInfo[2]);
            }
            $queryType = strtoupper(substr($this->statement, 0, 6));
            $result = null;
            switch($queryType){
                case 'SELECT':
                    $result = $stmt->fetchAll();
                    break;
                case 'INSERT':
                case 'UPDATE':
                case 'DELETE':
                    $result = $stmt->rowCount();
                    break;
                default:
                    $this->pdo->rollBack();
                    throw new Exception('Unsupported Query Type: '.$this->statement);
            }
            $this->pdo->commit();
            return $result;
        }catch(Exception $e){
            if($this->pdo->inTransaction()){ // Check if in transaction before rollback
                $this->pdo->rollBack();
            }
            $errmessage = 'Query Failed :'.$e->getMessage().' - Statement: '.$this->statement;
            throw new Exception($errmessage);
        }
    }
    public function lastInsertId(){
        return $this->pdo->lastInsertId();
    }
}
/* Example code
try{
    $statement = new DBstatement('SELECT * FROM user WHERE username = :username');
    $execute = $statement->execute([':username' => 'admin']);
    print_r($execute);
}catch(Exception $e){
    echo $e->getMessage();
}
*/