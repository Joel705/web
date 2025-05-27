<?php
// Iniciamos la sesión para manejar carrito y mensajes
session_start();

// Incluimos la conexión a base de datos
require 'db.php';

// Creamos id de sesión único para identificar carrito
if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = session_id();
}

// Agregar producto al carrito al enviar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']) ?? 1;
    if ($quantity < 1) $quantity = 1;

    // Validar stock disponible
    $stmtStock = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmtStock->execute([$product_id]);
    $stock = $stmtStock->fetchColumn();

    if (!$stock || $stock < $quantity) {
        $_SESSION['error'] = "No hay suficiente stock disponible para ese producto.";
        header('Location: index.php?cat=' . ($_GET['cat'] ?? 'all'));
        exit;
    }

    // Verificar si producto ya está en carrito
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE product_id = ? AND session_id = ?");
    $stmt->execute([$product_id, $_SESSION['session_id']]);
    $item = $stmt->fetch();

    if ($item) {
        // Sumar cantidad sin pasar stock
        $new_qty = $item['quantity'] + $quantity;
        if ($new_qty > $stock) {
            $_SESSION['error'] = "No puedes agregar más que el stock disponible.";
            header('Location: index.php?cat=' . ($_GET['cat'] ?? 'all'));
            exit;
        }
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$new_qty, $item['id']]);
    } else {
        // Insertar nuevo producto en carrito
        $stmt = $pdo->prepare("INSERT INTO cart (product_id, quantity, session_id) VALUES (?, ?, ?)");
        $stmt->execute([$product_id, $quantity, $_SESSION['session_id']]);
    }

    // Mensaje éxito y redirigir para evitar resubmit
    $_SESSION['success'] = "Producto agregado al carrito.";
    header('Location: index.php?cat=' . ($_GET['cat'] ?? 'all'));
    exit;
}

// Categorías para menú y filtro
$categories = ['camisas' => 'Camisas', 'pantalones' => 'Pantalones', 'zapatos' => 'Zapatos', 'accesorios' => 'Accesorios'];

// Leer categoría seleccionada o mostrar todas
$selected_cat = $_GET['cat'] ?? 'all';
if (!array_key_exists($selected_cat, $categories) && $selected_cat !== 'all') {
    $selected_cat = 'all';
}

// Obtener productos según filtro
if ($selected_cat === 'all') {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY category, name");
    $products = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? ORDER BY name");
    $stmt->execute([$selected_cat]);
    $products = $stmt->fetchAll();
}

// Contar productos en carrito para mostrar en menú
$stmtCount = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE session_id = ?");
$stmtCount->execute([$_SESSION['session_id']]);
$cart_count = $stmtCount->fetchColumn() ?: 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Trendify - Tienda de ropa moderna</title>
  <link rel="stylesheet" href="style.css" />
  <script>
    // Script para abrir/cerrar menú hamburguesa en móvil
    document.addEventListener("DOMContentLoaded", function() {
      const toggle = document.querySelector('.nav-toggle');
      const menu = document.querySelector('.nav-menu');

      toggle.addEventListener('click', () => {
        menu.classList.toggle('open');
      });

      // Cerrar menú al hacer click en enlace
      document.querySelectorAll('.nav-menu a').forEach(link => {
        link.addEventListener('click', () => {
          menu.classList.remove('open');
        });
      });
    });
  </script>
</head>
<body>

<nav>
  <div class="nav-container">
    <div class="nav-logo">Trendify</div>
    <div class="nav-toggle" aria-label="Abrir menú" role="button" tabindex="0">
      <span></span>
      <span></span>
      <span></span>
    </div>
    <div class="nav-menu">
      <a href="index.php?cat=all" class="<?= $selected_cat === 'all' ? 'active' : '' ?>">Todos</a>
      <?php foreach ($categories as $cat_id => $cat_name): ?>
        <a href="index.php?cat=<?= $cat_id ?>" class="<?= $selected_cat === $cat_id ? 'active' : '' ?>"><?= $cat_name ?></a>
      <?php endforeach; ?>
      <a href="cart.php" class="cart-link">Carrito (<?= $cart_count ?>)</a>
    </div>
  </div>
</nav>

<div class="container">

  <!-- Mensajes -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="message success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="message error"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <!-- Productos -->
  <?php if (empty($products)): ?>
    <p>No hay productos para mostrar en esta categoría.</p>
  <?php else: ?>
    <div class="products-grid">
      <?php foreach ($products as $product): ?>
        <div class="product-card">
          <img src="images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
          <div class="product-info">
            <h3><?= htmlspecialchars($product['name']) ?></h3>
            <p>Precio: $<?= number_format($product['price'], 2) ?></p>
            <p>Stock disponible: <?= $product['stock'] ?></p>
          </div>
          <?php if ($product['stock'] > 0): ?>
            <form method="POST" action="index.php?cat=<?= $selected_cat ?>">
              <input type="hidden" name="product_id" value="<?= $product['id'] ?>" />
              <input type="number" class="quantity-input" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" />
              <button type="submit" class="btn">Añadir al carrito</button>
            </form>
          <?php else: ?>
            <button disabled class="btn" style="background:#888;cursor:not-allowed;">Agotado</button>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

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
