<?php
session_start();
ob_start(); // Start output buffering
include('includes/header.php');
include('config/dbcon.php');

// Check if user is logged in
if (!isset($_SESSION['auth_user']['account_id'])) {
    echo "<p class='text-center'>Please log in to view your cart.</p>";
    include('includes/footer.php');
    exit();
}

$account_id = $_SESSION['auth_user']['account_id'];

// Prepare and execute statement to fetch active cart items
$cart_query = "
    SELECT c.id, c.quantity, p.product_id, p.name, p.price, p.image 
    FROM cart c 
    JOIN products p ON c.product_id = p.product_id 
    WHERE c.account_id = ? AND c.status = 'active'
";

$stmt = mysqli_prepare($con, $cart_query);
mysqli_stmt_bind_param($stmt, "i", $account_id);
mysqli_stmt_execute($stmt);
$cart_result = mysqli_stmt_get_result($stmt);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    // Process the order
    echo "<p class='text-center'>Order placed successfully! Redirecting to checkout...</p>";
    // Redirect to the checkout page
    header('Location: checkout.php');
    exit();
}
?>
<style>
.removing {
    opacity: 0.5; /* Dim the item to show it's being removed */
    pointer-events: none; /* Disable interaction with the item while it's being removed */
}
</style>

<div class="container py-5">
    <h2 class="text-center text-white mb-4">Your Cart</h2>

    <?php if (mysqli_num_rows($cart_result) == 0): ?>
        <p class="text-center">Your cart is empty.</p>
    <?php else: ?>
        <form id="cart-form" method="POST">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    while ($item = mysqli_fetch_assoc($cart_result)):
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                    <tr data-cart-id="<?= $item['id'] ?>">
                        <td>
                            <img src="uploads/<?= htmlspecialchars($item['image']) ?>" width="50" alt="<?= htmlspecialchars($item['name']) ?>"> <?= htmlspecialchars($item['name']) ?>
                        </td>
                        <td>₱<?= number_format($item['price'], 2) ?></td>
                        <td>
                            <input type="number" class="form-control cart-qty" 
                                   value="<?= $item['quantity'] ?>" min="1" 
                                   data-cart-id="<?= $item['id'] ?>"  
                                   data-price="<?= $item['price'] ?>">
                        </td>
                        <td id="subtotal<?= $item['id'] ?>">₱<?= number_format($subtotal, 2) ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-item" data-cart-id="<?= $item['id'] ?>">
                                Remove
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="text-end text-white mt-4">
                <h4>Total: ₱<span id="cart-total"><?= number_format($total, 2) ?></span></h4>
                <button type="submit" name="place_order" class="btn btn-success mt-2">Proceed to Checkout</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<!-- SweetAlert 2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<!-- SweetAlert 2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
    document.querySelectorAll('.cart-qty').forEach(input => {
    input.addEventListener('change', function () {
        const cartId = this.dataset.cartId;
        const newQty = parseInt(this.value) || 1;
        const price = parseFloat(this.dataset.price);

        if (newQty < 1) {
            this.value = 1;
            return;
        }

        fetch('functions/handlecart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `cart_id=${cartId}&quantity=${newQty}&scope=update`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' || data.status === 'no_change') {
                const newSubtotal = price * newQty;
                document.getElementById('subtotal' + cartId).textContent = `₱${newSubtotal.toFixed(2)}`;

                let newTotal = 0;
                document.querySelectorAll('.cart-qty').forEach(qtyInput => {
                    const unitPrice = parseFloat(qtyInput.dataset.price);
                    const quantity = parseInt(qtyInput.value) || 1;
                    newTotal += unitPrice * quantity;
                });
                document.getElementById('cart-total').textContent = newTotal.toFixed(2);
            } else {
                alert('Error updating quantity.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update cart. Try again.');
        });
    });
});

document.querySelectorAll('.remove-item').forEach(button => {
    button.addEventListener('click', function () {
        const cartId = this.dataset.cartId;

        // Show SweetAlert for confirmation
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to remove this item from your cart.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove it!',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) {
                // Call API to remove item from the cart
                fetch('functions/handlecart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `cart_id=${cartId}&scope=delete`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        Swal.fire(
                            'Removed!',
                            'Item has been removed from your cart.',
                            'success'
                        );
                        // Dynamically update the cart section
                        updateCartDisplay(data.cart_items);
                    } else {
                        Swal.fire(
                            'Error!',
                            'Something went wrong while removing the item.',
                            'error'
                        );
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire(
                        'Error!',
                        'Something went wrong while removing the item.',
                        'error'
                    );
                });
            }
        });
    });
});

// Function to update the cart section after removing an item
function updateCartDisplay(cartItems) {
    const cartContainer = document.getElementById('cart-container');
    if (!cartContainer) return; // Safety check
    cartContainer.innerHTML = '';  // Clear existing cart items

    if (cartItems.length === 0) {
        cartContainer.innerHTML = '<p>Your cart is empty.</p>';
    } else {
        cartItems.forEach(item => {
            const cartItem = document.createElement('div');
            cartItem.classList.add('cart-item');
            cartItem.innerHTML = `
                <p>Product ID: ${item.product_id}</p>
                <p>Quantity: ${item.quantity}</p>
                <button class="remove-item" data-cart-id="${item.id}">Remove</button>
            `;
            cartContainer.appendChild(cartItem);
        });
    }
}
</script>
<?php
include('includes/footer.php');
ob_end_flush();
?>
