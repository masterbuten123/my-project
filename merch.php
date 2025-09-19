<?php
session_start();
include('includes/header.php');
include('config/dbcon.php');
include('functions/myfunctions.php');

$account_id = $_SESSION['auth_user']['account_id'] ?? null;

$products = [];
$result = mysqli_query($con, "SELECT * FROM products WHERE status='active'");
if ($result) $products = mysqli_fetch_all($result, MYSQLI_ASSOC);

var_dump($account_id);
?>

<div class="container mt-4">
    <div class="row g-4">
        <?php foreach ($products as $p): ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card h-100">
                <img src="<?= $p['image'] ? 'uploads/products/'.$p['image'] : 'assets/logo/default-product.png'; ?>" 
                     class="card-img-top" alt="<?= htmlspecialchars($p['name']); ?>" 
                     style="object-fit:cover; height:200px;" loading="lazy">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?= htmlspecialchars($p['name']); ?></h5>
                    <p class="card-text text-truncate"><?= htmlspecialchars($p['description']); ?></p>
                    <p class="card-text fw-bold">₱<?= number_format($p['price'],2); ?></p>
                    <div class="mt-auto d-flex align-items-center gap-2">
                        <input type="number" value="1" min="1" class="form-control quantity-input" 
                               style="width: 80px;" id="qty-<?= $p['product_id']; ?>">
                        <button class="btn btn-primary addToCartBtn" data-id="<?= $p['product_id']; ?>">Add to Cart</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Floating Cart Button -->
<button id="floatingCartBtn" class="btn btn-danger position-fixed rounded-circle" 
        style="bottom:30px; right:30px; width:60px; height:60px; z-index:9999;">
    🛒 <span id="cartCount" class="badge bg-warning position-absolute top-0 start-100 translate-middle">0</span>
</button>

<!-- Cart Sidebar -->
<div id="cartSidebar" class="position-fixed bg-white shadow p-3" 
     style="top:0; right:-400px; width:350px; height:100vh; z-index:9998;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>My Cart</h5>
        <button id="closeCart" class="btn-close"></button>
    </div>
    <div id="cartItems" class="mb-3"><p class="text-muted">Your cart is empty.</p></div>
    <div>
        <strong>Total: ₱<span id="cartTotal">0.00</span></strong>
        <a href="checkout.php" class="btn btn-primary w-100 mt-2">Checkout</a>
    </div>
</div>

<!-- Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100;">
    <div id="cartToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Cart</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const floatingCartBtn = document.getElementById('floatingCartBtn');
const cartSidebar = document.getElementById('cartSidebar');
const closeCart = document.getElementById('closeCart');
const cartItems = document.getElementById('cartItems');
const cartCount = document.getElementById('cartCount');
const cartTotal = document.getElementById('cartTotal');
const toast = new bootstrap.Toast(document.getElementById('cartToast'));
const toastBody = document.getElementById('toastMessage');

floatingCartBtn.addEventListener('click', () => { cartSidebar.style.right='0'; loadCart(); });
closeCart.addEventListener('click', () => cartSidebar.style.right='-400px');

// Load cart and render items
function loadCart() {
    fetch('functions/handlecart.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:new URLSearchParams({scope:'fetch'})
    })
    .then(res=>res.json())
    .then(data=>{
        cartItems.innerHTML = '';
        let total=0;
        if (data.items?.length) {
            data.items.forEach(i=>{
                total += i.price*i.quantity;
                cartItems.innerHTML += `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>${i.name} x ${i.quantity}</span>
                        <div>
                            <span>₱${(i.price*i.quantity).toFixed(2)}</span>
                            <button class="btn btn-sm btn-outline-danger removeItemBtn" data-id="${i.product_id}">&times;</button>
                        </div>
                    </div>`;
            });
            cartTotal.textContent = total.toFixed(2);
            cartCount.textContent = data.items.length;
        } else {
            cartItems.innerHTML = '<p class="text-muted">Your cart is empty.</p>';
            cartTotal.textContent='0.00';
            cartCount.textContent='0';
        }

        // Attach remove handlers
        document.querySelectorAll('.removeItemBtn').forEach(btn=>{
            btn.addEventListener('click', ()=>{
                const id = btn.dataset.id;
                fetch('functions/handlecart.php', {
                    method:'POST',
                    headers:{'Content-Type':'application/x-www-form-urlencoded'},
                    body:new URLSearchParams({scope:'remove', product_id:id})
                })
                .then(res=>res.json())
                .then(data=>{
                    toastBody.textContent = data.message;
                    toastEl=document.getElementById('cartToast');
                    toastEl.classList.remove('bg-success','bg-warning','bg-danger');
                    toastEl.classList.add(data.status==='success'?'bg-success':'bg-danger');
                    toast.show();
                    loadCart();
                });
            });
        });
    });
}

// Add to cart buttons
document.querySelectorAll('.addToCartBtn').forEach(btn=>{
    btn.addEventListener('click',()=>{
        const id=btn.dataset.id;
        let qty=parseInt(document.getElementById('qty-'+id).value)||1;

        fetch('functions/handlecart.php',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:new URLSearchParams({scope:'add', product_id:id, quantity:qty})
        })
        .then(res=>res.json())
        .then(data=>{
            toastBody.textContent = data.message;
            const toastEl=document.getElementById('cartToast');
            toastEl.classList.remove('bg-success','bg-warning','bg-danger');
            toastEl.classList.add(data.status==='201'?'bg-success':(data.status==='exists'?'bg-warning':'bg-danger'));
            toast.show();
            loadCart();
        });
    });
});
</script>

<style>
#cartSidebar { transition:right 0.3s ease-in-out; }
.card-text.text-truncate { overflow:hidden; white-space:nowrap; text-overflow:ellipsis; }
@media(max-width:576px){ #cartSidebar { width:100%; } }
</style>

<?php include('includes/footer.php'); ?>
