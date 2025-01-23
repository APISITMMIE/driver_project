<?php
$serverName = "BMT03\BMTSQLSERVER";   
$database = "BMT_MASTER";  
 
$uid = "sa";  
$pwd = "Thchsusi2012";  

try {  
   $conn2 = new PDO( "sqlsrv:server=$serverName;Database = $database", $uid, $pwd);   
   $conn2->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );   
}  

catch( PDOException $e ) {  
   die( "Error connecting to SQL Server" );   
}  
?>
