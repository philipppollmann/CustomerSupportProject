<?php

//function, welche die Daten der Datenbanken als Array zurückgibt
function creds() { 
    return [
        'philipp.mysql.database.azure.com', 3306,
        'philadmin', 'Canned0-Curling-Overfill-Exciting-Mollusk-Dynasty',
        'finaldatabase',
        './DigiCertGlobalRootCA.crt.pem'
    ]; 
}

//Neue connection wird gebaut und eine bestehende connection wird zurückgelifert
function newDBConn() {
    list($host, $port, $username, $password, $db_name, $ca_certs) = creds();
    
    $options = array(
        PDO::MYSQL_ATTR_SSL_CA => $ca_certs
    );
    $conn = new PDO("mysql:host=$host;port=".$port.";dbname=$db_name", $username, $password, $options);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	return $conn;
}

function createTable($sql) {
    list($host, $port, $username, $password, $db_name, $ca_certs) = creds();

    $conn = mysqli_init();
    mysqli_ssl_set($conn,NULL,NULL, $ca_certs, NULL, NULL);
    mysqli_real_connect($conn, $host, $username, $password, $db_name, $port);

    // only proceed if connection is healthy
    if ($conn->connect_error) {
    	die("MySQL Connection failed: " . $conn->connect_error);
    }

    $conn->select_db($db_name);

    if(!$conn->query($sql))
    {
    	echo($conn->error);
    }
}

?>