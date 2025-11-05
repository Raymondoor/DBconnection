# Raymondoor/DBconnection

## Quick start

```php
// include
use \Raymondoor\DBconnection;
use \Raymondoor\DBoperation;

// some data
$usrnm = 'admin';
$pswrd = password_hash('password', PASSWORD_DEFAULT);

// Initialize db. Only has to be run once.
DBconnection::init('sqlite:'.__DIR__.'/database.db');

// Here I am using a wrapper of DBconnection, called DBoperation.
DBoperation::makeTableIfNot('user',
    DBoperation::create_id().', 
    username TEXT, 
    password TEXT'
);

// Insert data. Returns affected rows.
$affectedrows = DBconnection::run(
    "INSERT INTO user (username, password) VALUES (:username, :password)",
    [':username' => $usrnm, ':password' => $pswrd]);
echo 'Affected Rows: '.$affectedrows."\n";

// Again using wrapper class, to fetch single data.
$oneuser = DBoperation::fetchOne('SELECT * FROM user');
var_dump($oneuser);
```
Output
```bash
Affected Rows: 1
array(3) {
  ["id"]=>
  int(1)
  ["username"]=>
  string(5) "admin"
  ["password"]=>
  string(60) "$2y$12$H0pdPDBPX7qHeV7yTwj7y./43yon.fYkdU9W9RFcoeDg/mEUZ76qq"
}
```