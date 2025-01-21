<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

// Fetch the user's cart items
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT cart.id AS cart_id, products.name, products.price, cart.quantity 
                        FROM cart 
                        JOIN products ON cart.product_id = products.id 
                        WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_cost = 0;

// Handle quantity update
if (isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$quantity, $cart_id, $user_id]);
    header("Location: cart.php"); // Refresh the page to reflect changes
    exit();
}

// Handle item removal
if (isset($_POST['remove_from_cart'])) {
    $cart_id = $_POST['cart_id'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    header("Location: cart.php"); // Refresh the page to reflect changes
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Your Shopping Cart</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
                <a href="cart.php" class="cart-link">Cart</a>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="logout" class="logout-button">Logout</button>
                </form>
            </nav>
        </div>
    </header>
    <div class="main-container">
        <main>
            <h2>Cart Items</h2>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cart_items)) : ?>
                        <tr>
                            <td colspan="5">No items in the cart.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($cart_items as $item) : ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']); ?></td>
                                <td>$<?= number_format($item['price'], 2); ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="cart_id" value="<?= $item['cart_id']; ?>">
                                        <input type="number" name="quantity" value="<?= $item['quantity']; ?>" min="1">
                                        <button type="submit" name="update_quantity">Update</button>
                                    </form>
                                </td>
                                <td>$<?= number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="cart_id" value="<?= $item['cart_id']; ?>">
                                        <button type="submit" name="remove_from_cart">Remove</button>
                                    </form>
                                </td>
                            </tr>
                            <?php $total_cost += $item['price'] * $item['quantity']; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Total Cost</strong></td>
                        <td colspan="2">$<?= number_format($total_cost, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </main>
    </div>
    <footer>
        <p>&copy; <?= date('Y'); ?> Online Store. All rights reserved.</p>
    </footer>
</body>
</html>
