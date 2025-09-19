<?php
session_start();
include('../config/dbcon.php'); // adjust path if needed

header('Content-Type: application/json');

$account_id = $_SESSION['auth_user']['account_id'] ?? null;
if (!$account_id) {
    echo json_encode(['status'=>'error','message'=>'You must be logged in.']);
    exit();
}

$scope = $_POST['scope'] ?? '';

switch ($scope) {

    // ADD TO CART
    case 'add':
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        if ($product_id <= 0 || $quantity <= 0) {
            echo json_encode(['status'=>'error','message'=>'Invalid product or quantity.']);
            exit();
        }

        // check if product already in cart
        $stmt = $con->prepare("SELECT id, quantity FROM cart WHERE account_id=? AND product_id=? AND status='active'");
        $stmt->bind_param("ii", $account_id, $product_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            // update quantity
            $row = $res->fetch_assoc();
            $newQty = $row['quantity'] + $quantity;
            $upd = $con->prepare("UPDATE cart SET quantity=? WHERE id=?");
            $upd->bind_param("ii", $newQty, $row['id']);
            $upd->execute();
            $upd->close();
            echo json_encode(['status'=>'exists','message'=>'Quantity updated in cart.']);
        } else {
            // insert new
            $ins = $con->prepare("INSERT INTO cart(account_id,product_id,quantity,status) VALUES(?,?,?, 'active')");
            $ins->bind_param("iii",$account_id,$product_id,$quantity);
            if($ins->execute()){
                echo json_encode(['status'=>'201','message'=>'Product added to cart.']);
            } else {
                echo json_encode(['status'=>'error','message'=>'Failed to add product.']);
            }
            $ins->close();
        }
        $stmt->close();
        break;

    // REMOVE FROM CART
    case 'remove':
        $product_id = intval($_POST['product_id'] ?? 0);
        if ($product_id <= 0) {
            echo json_encode(['status'=>'error','message'=>'Invalid product.']);
            exit();
        }

        $del = $con->prepare("DELETE FROM cart WHERE account_id=? AND product_id=?");
        $del->bind_param("ii", $account_id, $product_id);
        if ($del->execute()) {
            echo json_encode(['status'=>'success','message'=>'Item removed from cart.']);
        } else {
            echo json_encode(['status'=>'error','message'=>'Failed to remove item.']);
        }
        $del->close();
        break;

    // UPDATE QUANTITY
    case 'update':
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        if ($product_id <= 0 || $quantity <= 0) {
            echo json_encode(['status'=>'error','message'=>'Invalid product or quantity.']);
            exit();
        }

        $upd = $con->prepare("UPDATE cart SET quantity=? WHERE account_id=? AND product_id=?");
        $upd->bind_param("iii", $quantity, $account_id, $product_id);
        if ($upd->execute()) {
            echo json_encode(['status'=>'success','message'=>'Quantity updated.']);
        } else {
            echo json_encode(['status'=>'error','message'=>'Failed to update quantity.']);
        }
        $upd->close();
        break;

    // FETCH CART
    case 'fetch':
        $res = $con->prepare("
            SELECT c.product_id, c.quantity, p.name, p.price, p.image
            FROM cart c 
            JOIN products p ON c.product_id = p.product_id
            WHERE c.account_id=? AND c.status='active'
        ");
        $res->bind_param("i",$account_id);
        $res->execute();
        $result = $res->get_result();

        $items = [];
        while($row = $result->fetch_assoc()){
            $items[] = $row;
        }
        echo json_encode(['status'=>'success','items'=>$items]);
        $res->close();
        break;

    default:
        echo json_encode(['status'=>'error','message'=>'Invalid request.']);
        break;
}
?>
