<?php
//Dato de conexion a la base de datos

function conexion(){
    $hostname = $_ENV['DB_HOST'] ?? 'localhost';
    $username = $_ENV['DB_USER'] ?? 'root';
    $password = $_ENV['DB_PASS'] ?? '';
    $database = $_ENV['DB_NAME'] ?? 'gico';
    $port = $_ENV['DB_PORT'] ?? '3306';

    $conexion = mysqli_connect($hostname, $username, $password, $database, $port);

    return $conexion;
}
