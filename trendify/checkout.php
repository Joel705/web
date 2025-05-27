<?php
// Iniciar sesión para manejar carrito y ventas
session_start();
require 'db.php';

// Validar que la sesión existe
if (!isset($_SESSION['session_id'])) {
    die("No hay sesión activa.");
}

// Obtener total de compra en carrito
$stmt = $pdo->prepare("
SELECT SUM(products.price * cart.quantity) AS total
FROM cart 
JOIN products ON cart.product_id = products.id
WHERE cart.session_id = ?");
$stmt->execute([$_SESSION['session_id']]);
$total = $stmt->fetchColumn();

// Si carrito vacío, no continuar
if (!$total) {
    die("No tienes productos en el carrito.");
}

// Insertar venta en tabla sales
$stmt = $pdo->prepare("INSERT INTO sales (session_id, total) VALUES (?, ?)");
$stmt->execute([$_SESSION['session_id'], $total]);

// Vaciar carrito de la sesión actual
$stmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ?");
$stmt->execute([$_SESSION['session_id']]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Compra finalizada - Trendify</title>
<link rel="stylesheet" href="style.css" />
</head>
<body>

<header>Trendify - Compra finalizada</header>

<div class="container">
    <h2>¡Gracias por tu compra!</h2>
    <p>Tu pedido ha sido procesado correctamente.</p>
    <a href="index.php" class="btn">Seguir comprando</a>
</div>

<footer>
  <p><strong>Trendify S.A. de C.V.</strong></p>
  <p>Johel Enrique Ortiz Chocoteco - Director General</p>
  <p>Av. Falsa 123, Ciudad Imaginaria, México</p>
  <p>Tel: +52 55 1234 5678 | Email: contacto@trendify.com.mx</p>
  <div class="social-buttons">
    <a href="#" title="Facebook" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
      <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.675 0h-21.35C.6 0 0 .6 0 1.325v21.351C0 23.4.6 24 1.325 24h11.495v-9.294H9.691v-3.622h3.129V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.466.099 2.797.143v3.24l-1.918.001c-1.504 0-1.796.715-1.796 1.763v2.31h3.587l-.467 3.622h-3.12V24h6.116C23.4 24 24 23.4 24 22.675V1.325C24 .6 23.4 0 22.675 0z"/></svg>
    </a>
    <a href="#" title="Instagram" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
      <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M7.75 2h8.5C19.55 2 22 4.45 22 7.75v8.5C22 19.55 19.55 22 16.25 22h-8.5C4.45 22 2 19.55 2 16.25v-8.5C2 4.45 4.45 2 7.75 2zm0 2C5.955 4 4 5.955 4 7.75v8.5C4 18.045 5.955 20 7.75 20h8.5c1.795 0 3.75-1.955 3.75-3.75v-8.5c0-1.795-1.955-3.75-3.75-3.75h-8.5zM12 7a5 5 0 110 10 5 5 0 010-10zm0 2a3 3 0 100 6 3 3 0 000-6zm4.75-3a1.25 1.25 0 110 2.5 1.25 1.25 0 010-2.5z"/></svg>
    </a>
    <a href="#" title="X (Twitter)" aria-label="X (Twitter)" target="_blank" rel="noopener noreferrer">
      <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M23 3a10.9 10.9 0 01-3.14.86 4.48 4.48 0 001.98-2.48 10.76 10.76 0 01-3.4 1.3A4.52 4.52 0 0016.07 2a4.48 4.48 0 00-4.48 4.48c0 .35.04.7.11 1.03A12.9 12.9 0 013 4.15a4.48 4.48 0 001.39 5.98 4.41 4.41 0 01-2.04-.57v.06a4.48 4.48 0 003.6 4.4 4.5 4.5 0 01-2.03.07 4.48 4.48 0 004.18 3.13A9 9 0 013 19.54a12.73 12.73 0 006.92 2.03c8.3 0 12.85-6.87 12.85-12.85 0-.2 0-.42-.02-.62A9.16 9.16 0 0023 3z"/></svg>
    </a>
  </div>
  <p style="margin-top:10px;">© 2025 Trendify. Todos los derechos reservados.</p>
</footer>

</body>
</html>
