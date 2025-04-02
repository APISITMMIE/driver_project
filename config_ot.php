<<<<<<< HEAD
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
=======
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
>>>>>>> e1c9b9236d4ed67f1dee5d6544511c0824532c22
