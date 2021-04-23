<?php
// Prevent direct access to file
defined('shoppingcart') or exit;
// check if customer is logged-in...
if (isset($_SESSION['account_loggedin'])) {
    // Remove the session variables
    unset($_SESSION['account_loggedin']);
    unset($_SESSION['account_id']);
    unset($_SESSION['account_admin']);
}
// Redirect to home page
header('Location: ' . url('index.php'));
?>
