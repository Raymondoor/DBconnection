<?php

require_once(__DIR__.'/../DBconnection.php');
require_once(__DIR__.'/../DBoperation.php');

use \Raymondoor\DBconnection;
use \Raymondoor\DBoperation;

$usrnm = 'admin';
$pswrd = password_hash('password', PASSWORD_DEFAULT);

try{
    DBconnection::init('sqlite:'.__DIR__.'/database.db');

    DBoperation::makeTableIfNot('user',
        DBoperation::create_id().', 
        username TEXT, 
        password TEXT'
    );

    $affectedrows = DBconnection::run(
        "INSERT INTO user (username, password) VALUES (:username, :password)",
        [':username' => $usrnm, ':password' => $pswrd]);
    echo 'Affected Rows: '.$affectedrows."\n";
    $oneuser = DBoperation::fetchOne('SELECT * FROM user');
    var_dump($oneuser);
}catch(Exception $e){
    echo $e->getMessage();
}