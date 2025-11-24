<?php

$server = "localhost";
$user = "root";
$password = "";
$bd = "blockbet";

$conexion = new mysqli($server, $user, $password, $bd);

if ($conexion->connect_errno) {
    die("Fallo la conexion: " . $conexion->connect_errno);
} else {
    echo "";
}

?>