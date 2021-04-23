<?php
error_reporting(E_ALL); // Error/Exception engine, always use E_ALL
ini_set('ignore_repeated_errors', TRUE); // always use TRUE
ini_set('log_errors', TRUE); // Error/Exception file logging engine.
ini_set('error_log', 'errors.log'); // Logging file path
include '../config.php';
include '../functions.php';
// Include stripe lib
require_once('../lib/stripe/init.php');
\Stripe\Stripe::setApiKey(stripe_secret_key);
$endpoint_secret = $_GET['key'];
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;
try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch(\UnexpectedValueException $e) {
    http_response_code(400);
    exit;
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    exit;
}
// Check whether the customer completed the checkout process
if ($event->type == 'checkout.session.completed') {
    $intent = $event->data->object;
    $stripe = new \Stripe\StripeClient(stripe_secret_key);
    // Transaction is verified and successful...
    $pdo = pdo_connect_mysql();
    $products_in_cart = [];
    $subtotal = 0.00;
    $shippingtotal = 0.00;
    $line_items = $stripe->checkout->sessions->allLineItems($intent->id);
    // Iterate the cart items and insert the transaction items into the MySQL database
    foreach ($line_items->data as $line_item) {
        // Retrieve product metadata
        $product = $stripe->products->retrieve($line_item->price->product);
        // Product related variables
        $item_options = isset($product->metadata->item_options) ? $product->metadata->item_options : '';
        $item_shipping = isset($product->metadata->item_shipping) ? $product->metadata->item_shipping : 0.00;
        // No need to include the shipping 
        if ($product->metadata->item_id == 'shipping') {
            continue;
        }
        // Update product quantity in the products table
        $stmt = $pdo->prepare('UPDATE products SET quantity = quantity - ? WHERE quantity > 0 AND id = ?');
        $stmt->execute([ $line_item->quantity, $product->metadata->item_id ]);
        // Insert product into the "transactions_items" table
        $stmt = $pdo->prepare('INSERT INTO transactions_items (txn_id, item_id, item_price, item_quantity, item_options, item_shipping_price) VALUES (?,?,?,?,?,?)');
        $stmt->execute([ $intent->payment_intent, $product->metadata->item_id, floatval($line_item->price->unit_amount) / 100, $line_item->quantity, $item_options, $item_shipping ]);
        // Add product to array
        $products_in_cart[] = [
            'id' => $product->metadata->item_id,
            'quantity' => $line_item->quantity,
            'options' => $item_options,
            'meta' => [
                'name' => $line_item->description,
                'price' => floatval($line_item->price->unit_amount) / 100
            ]
        ];
        // Add product price to the subtotal variable
        $subtotal += floatval($line_item->price->unit_amount) / 100;
        // Add product shipping to the total shipping variable
        $shippingtotal += floatval($item_shipping);
    }
    // Insert the transaction into our transactions table
    $stmt = $pdo->prepare('INSERT INTO transactions (txn_id, payment_amount, payment_status, created, payer_email, first_name, last_name, address_street, address_city, address_state, address_zip, address_country, account_id, payment_method) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE payment_status = VALUES(payment_status)');
    $stmt->execute([
        $intent->payment_intent,
        $subtotal+$shippingtotal,
        'Completed',
        date('Y-m-d H:i:s'),
        $intent->customer_email,
        $intent->metadata->first_name,
        $intent->metadata->last_name,
        $intent->metadata->address_street,
        $intent->metadata->address_city,
        $intent->metadata->address_state,
        $intent->metadata->address_zip,
        $intent->metadata->address_country,
        $intent->metadata->account_id,
        'stripe'
    ]);
    $order_id = $pdo->lastInsertId();
    // Send order details to the customer's email address
    send_order_details_email(
        $intent->customer_email,
        $products_in_cart,
        $intent->metadata->first_name,
        $intent->metadata->last_name,
        $intent->metadata->address_street,
        $intent->metadata->address_city,
        $intent->metadata->address_state,
        $intent->metadata->address_zip,
        $intent->metadata->address_country,
        $subtotal+$shippingtotal,
        $order_id
    );
}
?>
