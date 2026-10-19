<?php
$servername = "localhost";
$username   = "root";      
$password   = "";          
$dbname     = "db_desa_ujungberung";

define('APP_DEBUG', true);

if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
?>