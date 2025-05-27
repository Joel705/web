<?php
// Inicia la sesión para manejar el carrito
session_start();

// Incluye la conexión a la base de datos
require 'db.php';

// Validación: si no hay sesión activa, no se puede continuar
if (!isset($_SESSION['session_id'])) {
    die("No hay sesión activa.");
}

// Vaciar carrito si se solicita
if (isset($_GET['clear']) && $_GET['clear'] == '1') {
    $stmtClear = $pdo->prepare("DELETE FROM cart WHERE session_id = ?");
    $stmtClear->execute([$_SESSION['session_id']]);
    $_SESSION['success'] = "Carrito vaciado correctamente.";
    header('Location: cart.php');
    exit;
}

// Eliminar producto individual del carrito
if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND session_id = ?");
    $stmt->execute([$remove_id, $_SESSION['session_id']]);
    $_SESSION['success'] = "Producto eliminado del carrito.";
    header('Location: cart.php');
    exit;
}

// Actualizar cantidades del carrito con validación de stock
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $cart_id => $qty) {
        $qty = intval($qty);
        if ($qty < 1) $qty = 1;

        // Obtener stock disponible para validar cantidad
        $stmtProd = $pdo->prepare("
            SELECT p.stock 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.id = ? AND c.session_id = ?
        ");
        $stmtProd->execute([$cart_id, $_SESSION['session_id']]);
        $stock = $stmtProd->fetchColumn();

        if ($qty > $stock) {
            $_SESSION['error'] = "No puedes poner más cantidad que el stock disponible.";
            header('Location: cart.php');
            exit;
        }

        // Actualizar cantidad en carrito
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND session_id = ?");
        $stmt->execute([$qty, $cart_id, $_SESSION['session_id']]);
    }
    $_SESSION['success'] = "Carrito actualizado correctamente.";
    header('Location: cart.php');
    exit;
}

// Obtener todos los productos en carrito con detalles para mostrar
$stmt = $pdo->prepare("
SELECT cart.id as cart_id, products.name, products.price, products.image, cart.quantity, products.stock
FROM cart 
JOIN products ON cart.product_id = products.id
WHERE cart.session_id = ?");
$stmt->execute([$_SESSION['session_id']]);
$items = $stmt->fetchAll();

// Calcular total de la compra
$total = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Carrito - Trendify</title>
<link rel="stylesheet" href="style.css" />
</head>
<body>

<header>Trendify - Carrito</header>

<div class="container">
    <a href="index.php" class="btn" style="width:auto;margin-bottom:20px;">Seguir comprando</a>

    <!-- Mensajes -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Mostrar carrito o mensaje vacío -->
    <?php if (count($items) === 0): ?>
        <p>Tu carrito está vacío.</p>
    <?php else: ?>
        <form method="POST" action="cart.php">
            <table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #ddd;">
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Stock</th>
                        <th>Subtotal</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td>
                            <img src="images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:60px;vertical-align:middle; border-radius:6px; margin-right:10px;">
                            <?= htmlspecialchars($item['name']) ?>
                        </td>
                        <td>$<?= number_format($item['price'], 2) ?></td>
                        <td>
                            <input class="quantity" type="number" name="quantities[<?= $item['cart_id'] ?>]" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>">
                        </td>
                        <td><?= $item['stock'] ?></td>
                        <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        <td>
                            <a href="cart.php?remove=<?= $item['cart_id'] ?>" style="color:#b00; font-weight:bold;">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p style="text-align:right; font-weight:bold; font-size:1.2rem; margin-top:20px;">
                Total: $<?= number_format($total, 2) ?>
            </p>

            <button type="submit" class="btn" style="margin-bottom:10px;">Actualizar cantidades</button>
        </form>

        <a href="cart.php?clear=1" class="btn" style="background:#b00; margin-bottom:20px;">Vaciar carrito</a>

        <a href="checkout.php" class="btn" style="width:100%;">Finalizar compra</a>
    <?php endif; ?>
</div>

</body>
</html>
