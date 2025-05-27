<?php
// Archivo que crea la conexión con la base de datos MySQL usando PDO

// Datos de conexión
$host = 'localhost';          // Servidor de base de datos (normalmente localhost)
$dbname = 'trendify';         // Nombre de la base de datos que creamos
$user = 'root';               // Usuario MySQL (root por defecto en local)
$pass = '';                   // Contraseña (vacía en local por defecto)

// Construimos la cadena DSN para PDO
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    // Creamos la conexión PDO con opciones para manejar errores con excepciones
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Si falla la conexión, mostramos el error y terminamos el script
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
