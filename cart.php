<?php
// Prevent direct access to file
defined('shoppingcart') or exit;
// If the user clicked the add to cart button on the product page we can check for the form data
if (isset($_POST['product_id'], $_POST['quantity']) && is_numeric($_POST['product_id']) && is_numeric($_POST['quantity'])) {
    // Set the post variables so we easily identify them, also make sure they are integer
    $product_id = (int)$_POST['product_id'];
    // abs() function will prevent minus quantity and (int) will make sure the value is an integer
    $quantity = abs((int)$_POST['quantity']);
    // Get product options
    $options = '';
    $options_price = 0.00;
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'option-') !== false) {
            $options .= str_replace('option-', '', $k) . '-' . $v . ',';
            $stmt = $pdo->prepare('SELECT * FROM products_options WHERE title = ? AND name = ? AND product_id = ?');
            $stmt->execute([ str_replace('option-', '', $k), $v, $product_id ]);
            $option = $stmt->fetch(PDO::FETCH_ASSOC);
            $options_price += $option['price'];
        }
    }
    $options = rtrim($options, ',');
    // Prepare the SQL statement, we basically are checking if the product exists in our database
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([ $_POST['product_id'] ]);
    // Fetch the product from the database and return the result as an Array
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    // Check if the product exists (array is not empty)
    if ($product && $quantity > 0) {
        // Product exists in database, now we can create/update the session variable for the cart
        if (!isset($_SESSION['cart'])) {
            // Shopping cart session variable doesnt exist, create it
            $_SESSION['cart'] = [];
        }
        $cart_product = &get_cart_product($product_id, $options);
        if ($cart_product) {
            // Product exists in cart, update the quanity
            $cart_product['quantity'] += $quantity;
        } else {
            // Product is not in cart, add it
            $_SESSION['cart'][] = [
                'id' => $product_id,
                'quantity' => $quantity,
                'options' => $options,
                'options_price' => $options_price,
                'shipping_price' => 0.00
            ];
        }
    }
    // Prevent form resubmission...
    header('location: ' . url('index.php?page=cart'));
    exit;
}
// Remove product from cart, check for the URL param "remove", this is the product id, make sure it's a number and check if it's in the cart
if (isset($_GET['remove']) && is_numeric($_GET['remove']) && isset($_SESSION['cart']) && isset($_SESSION['cart'][$_GET['remove']])) {
    // Remove the product from the shopping cart
    array_splice($_SESSION['cart'], $_GET['remove'], 1);
    header('location: ' . url('index.php?page=cart'));
    exit;
}
// Empty the cart
if (isset($_POST['emptycart']) && isset($_SESSION['cart'])) {
    // Remove all products from the shopping cart
    unset($_SESSION['cart']);
    header('location: ' . url('index.php?page=cart'));
    exit;
}
// Update product quantities in cart if the user clicks the "Update" button on the shopping cart page
if ((isset($_POST['update']) || isset($_POST['checkout'])) && isset($_SESSION['cart'])) {
    // Iterate the post data and update quantities for every product in cart
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'quantity') !== false && is_numeric($v)) {
            $id = str_replace('quantity-', '', $k);
            // abs() function will prevent minus quantity and (int) will make sure the number is an integer
            $quantity = abs((int)$v);
            // Always do checks and validation
            if (is_numeric($id) && isset($_SESSION['cart'][$id]) && $quantity > 0) {
                // Update new quantity
                $_SESSION['cart'][$id]['quantity'] = $quantity;
            }
        }
    }
    // Update shipping method
    if (isset($_POST['shipping_method'])) {
        $_SESSION['shipping_method'] = $_POST['shipping_method'];
    }
    // Update discount code
    if (isset($_POST['discount_code']) && !empty($_POST['discount_code'])) {
        $_SESSION['discount'] = $_POST['discount_code'];
    } else if (isset($_POST['discount_code']) && empty($_POST['discount_code']) && isset($_SESSION['discount'])) {
        unset($_SESSION['discount']);
    }
    // Send the user to the place order page if they click the Place Order button, also the cart should not be empty
    if (isset($_POST['checkout']) && !empty($_SESSION['cart'])) {
        header('Location: ' . url('index.php?page=checkout'));
        exit;
    }
    header('location: ' . url('index.php?page=cart'));
    exit;
}
// Check the session variable for products in cart
$products_in_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = 0.00;
$discounttotal = 0.00;
$shippingtotal = 0.00;
$selected_shipping_method = isset($_SESSION['shipping_method']) ? $_SESSION['shipping_method'] : null;
$shipping_available = false;
// If there are products in cart
if ($products_in_cart) {
    // There are products in the cart so we need to select those products from the database
    // Products in cart array to question mark string array, we need the SQL statement to include: IN (?,?,?,...etc)
    $array_to_question_marks = implode(',', array_fill(0, count($products_in_cart), '?'));
    $stmt = $pdo->prepare('SELECT p.id, pc.category_id, p.* FROM products p LEFT JOIN products_categories pc ON p.id = pc.product_id LEFT JOIN categories c ON c.id = pc.category_id WHERE p.id IN (' . $array_to_question_marks . ') GROUP BY p.id');
    // We use the array_column to retrieve only the id's of the products
    $stmt->execute(array_column($products_in_cart, 'id'));
    // Fetch the products from the database and return the result as an Array
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Retrieve the discount code
    if (isset($_SESSION['discount'])) {
        $stmt = $pdo->prepare('SELECT * FROM discounts WHERE discount_code = ?');
        $stmt->execute([ $_SESSION['discount'] ]);
        $discount = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Get the current date
    $current_date = strtotime((new DateTime())->format('Y-m-d H:i:s'));
    // Retrieve shipping methods
    $stmt = $pdo->query('SELECT * FROM shipping');
    $shipping_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $selected_shipping_method = $selected_shipping_method == null && $shipping_methods ? $shipping_methods[0]['name'] : $selected_shipping_method;
    // Iterate the products in cart and add the meta data (product name, desc, etc)
    foreach ($products_in_cart as &$cart_product) {
        foreach ($products as $product) {
            if ($cart_product['id'] == $product['id']) {
                $cart_product['meta'] = $product;
                // Calculate the subtotal
                $product_price = $cart_product['options_price'] > 0 ? (float)$cart_product['options_price'] : (float)$product['price'];
                $subtotal += $product_price * (int)$cart_product['quantity'];
                // Calculate the shipping
                foreach ($shipping_methods as $shipping_method) {
                    if ($shipping_method['name'] == $selected_shipping_method && $product_price >= $shipping_method['price_from'] && $product_price <= $shipping_method['price_to'] && $product['weight'] >= $shipping_method['weight_from'] && $product['weight'] <= $shipping_method['weight_to']) {
                        $shippingtotal += (float)$shipping_method['price'] * (int)$cart_product['quantity'];
                        $shipping_available = true;
                    } else if ($product_price >= $shipping_method['price_from'] && $product_price <= $shipping_method['price_to'] && $product['weight'] >= $shipping_method['weight_from'] && $product['weight'] <= $shipping_method['weight_to']) {
                        $shipping_available = true;
                    }
                }
                // Calculate the discount
                if (isset($discount) && $discount && $current_date >= strtotime($discount['start_date']) && $current_date <= strtotime($discount['end_date'])) {
                    $category_ids = explode(',', $discount['category_ids']);
                    $product_ids = explode(',', $discount['product_ids']);
                    if ((!$discount['category_ids'] && !$discount['product_ids']) || in_array($product['id'], $product_ids) || in_array($product['category_id'], $category_ids)) {
                        $discounttotal += $product_price * (int)$cart_product['quantity'];
                    }
                }
            }
        }
    }
    // Update the discount total
    if ($discounttotal > 0) {
        if ($discount['discount_type'] == 'Percentage') {
            $discounttotal -= $discounttotal - ($discounttotal * ((float)$discount['discount_value']/100));
        }
        if ($discount['discount_type'] == 'Fixed') {
            $discounttotal = (float)$discount['discount_value'];
        }
    }
}
?>
<?=template_header('Shopping Cart')?>

<div class="cart content-wrapper">

    <h1>Shopping Cart</h1>

    <form action="" method="post">
        <table>
            <thead>
                <tr>
                    <td colspan="2">Product</td>
                    <td></td>
                    <td class="rhide">Price</td>
                    <td>Quantity</td>
                    <td>Total</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products_in_cart)): ?>
                <tr>
                    <td colspan="6" style="text-align:center;">You have no products added in your Shopping Cart</td>
                </tr>
                <?php else: ?>
                <?php foreach ($products_in_cart as $num => $product): ?>
                <tr>
                    <td class="img">
                        <?php if (!empty($product['meta']['img']) && file_exists('imgs/' . $product['meta']['img'])): ?>
                        <a href="<?=url('index.php?page=product&id=' . $product['id'])?>">
                            <img src="<?=base_url?>imgs/<?=$product['meta']['img']?>" width="50" height="50" alt="<?=$product['meta']['name']?>">
                        </a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?=url('index.php?page=product&id=' . $product['id'])?>"><?=$product['meta']['name']?></a>
                        <br>
                        <a href="<?=url('index.php?page=cart&remove=' . $num)?>" class="remove">Remove</a>
                    </td>
                    <td class="price">
                        <?=$product['options']?>
                        <input type="hidden" name="options" value="<?=$product['options']?>">
                    </td>
                    <?php if ($product['options_price'] > 0): ?>
                    <td class="price rhide"><?=currency_code?><?=number_format($product['options_price'],2)?></td>
                    <?php else: ?>
                    <td class="price rhide"><?=currency_code?><?=number_format($product['meta']['price'],2)?></td>
                    <?php endif; ?>
                    <td class="quantity">
                        <input type="number" class="ajax-update" name="quantity-<?=$num?>" value="<?=$product['quantity']?>" min="1" <?php if ($product['meta']['quantity'] != -1): ?>max="<?=$product['meta']['quantity']?>"<?php endif; ?> placeholder="Quantity" required>
                    </td>
                    <?php if ($product['options_price'] > 0): ?>
                    <td class="price product-total"><?=currency_code?><?=number_format($product['options_price'] * $product['quantity'],2)?></td>
                    <?php else: ?>
                    <td class="price product-total"><?=currency_code?><?=number_format($product['meta']['price'] * $product['quantity'],2)?></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (isset($shipping_methods) && $shipping_available): ?>
        <div class="shipping-methods">
            <h2>Shipping Method</h2>
            <?php foreach(array_unique(array_column($shipping_methods, 'name')) as $k => $v): ?>
            <div class="shipping-method">
                <input type="radio" class="ajax-update" id="sm<?=$k?>" name="shipping_method" value="<?=$v?>"<?=$selected_shipping_method==$v?' checked':''?>>
                <label for="sm<?=$k?>"><?=$v?></label>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="discount-code">
            <input type="text" class="ajax-update" name="discount_code" placeholder="Discount Code" value="<?=isset($_SESSION['discount']) ? $_SESSION['discount'] : ''?>">
            <span class="result">
                <?php if (isset($_SESSION['discount'], $discount) && !$discount): ?>
                Incorrect discount code!
                <?php elseif (isset($_SESSION['discount'], $discount) && $current_date > strtotime($discount['end_date'])): ?>
                Discount code expired!
                <?php endif; ?>
            </span>
        </div>

        <div class="subtotal">
            <span class="text">Subtotal</span>
            <span class="price"><?=currency_code?><?=number_format($subtotal,2)?></span>
        </div>

        <div class="shipping">
            <span class="text">Shipping</span>
            <span class="price"><?=currency_code?><?=number_format($shippingtotal,2)?></span>
        </div>

        <div class="discount">
            <?php if ($discounttotal > 0): ?>
            <span class="text">Discount</span>
            <span class="price">-<?=currency_code?><?=number_format($discounttotal,2)?></span>
            <?php endif; ?>
        </div>

        <div class="total">
            <span class="text">Total</span>
            <span class="price"><?=currency_code?><?=number_format(($subtotal-round($discounttotal,2))+$shippingtotal,2)?></span>
        </div>

        <div class="buttons">
            <input type="submit" value="Update" name="update">
            <input type="submit" value="Empty Cart" name="emptycart">
            <input type="submit" value="Checkout" name="checkout">
        </div>

    </form>

</div>

<?=template_footer()?>
