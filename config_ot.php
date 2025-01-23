<?php
$serverName =;   
$database = ;  
 
$uid = ;  
$pwd = ;  

try {  
   $conn2 = new PDO( "sqlsrv:server=$serverName;Database = $database", $uid, $pwd);   
   $conn2->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );   
}  

catch( PDOException $e ) {  
   die( "Error connecting to SQL Server" );   
}  
?>
