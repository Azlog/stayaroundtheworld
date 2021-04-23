<?php
// Prevent direct access to file
defined('shoppingcart') or exit;
// Disable the time limit (for large files)
set_time_limit(0);
// Customer must be logged-in and must provide the secret ID
if (isset($_GET['id'], $_SESSION['account_loggedin'])) {
    // Get the product download URL and check if the ID matches
    $stmt = $pdo->prepare('SELECT p.* FROM products p JOIN transactions t ON t.account_id = ? AND MD5(t.txn_id) = ? JOIN transactions_items ti ON ti.item_id = p.id AND ti.txn_id = t.txn_id');
    $stmt->execute([ $_SESSION['account_id'], $_GET['id'] ]);
    // Fetch the product from the database and return the result as an Array
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product || !$product['download_url']) {
        exit('Invalid ID!');
    }
} else {
    exit('Invalid ID!');
}
// Create the headers for the downloadable file
header('Pragma: public');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: public');
header('Content-Description: File Transfer');
header('Content-type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($product['download_url']) . '"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($product['download_url']));
ob_end_flush();
// Download file
@readfile($product['download_url']);
exit;
?>
