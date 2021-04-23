<?php
/* DATABASE SETTINGS */
// Database hostname, don't change this unless your hostname is different
define('db_host','localhost');
// Database username
define('db_user','root');
// Database password
define('db_pass','');
// Database name
define('db_name','shoppingcart_advanced');

/* GENERAL SETTINGS */
// This will change the title on the website
define('site_name','Shopping Cart');
// Currency code, default is USD, you can view the list here: http://cactus.io/resources/toolbox/html-currency-symbol-codes
define('currency_code','&dollar;');
// Featured image URL
define('featured_image','imgs/featured-image.jpg');
// Account required for checkout?
define('account_required',true);
// The from email that will appear on the customer's order details email
define('mail_from','noreply@yourwebsite.com');
// Send mail to the customers, etc?
define('mail_enabled',true);
// Rewrite URL?
define('rewrite_url',false);

/* PAYPAL SETTINGS */
// Accept payments with PayPal?
define('paypal_enabled',true);
// Your business email account, this is where you'll receive the money
define('paypal_email','payments@yourwebsite.com');
// If the test mode is set to true it will use the PayPal sandbox website, which is used for testing purposes.
// Read more about PayPal sandbox here: https://developer.paypal.com/developer/accounts/
// Set this to false when you're ready to start accepting payments on your business account
define('paypal_testmode',true);
// Currency to use with PayPal, default is USD
define('paypal_currency','USD');
// PayPal IPN url, this should point to the IPN file located in the "ipn" directory
define('paypal_ipn_url','https://yourwebsite.com/ipn/paypal.php');
// PayPal cancel URl, the page the customer returns to when they cancel the payment
define('paypal_cancel_url','https://yourwebsite.com/cart');
// PayPal return URL, the page the customer returns to after the payment has been made:
define('paypal_return_url','https://yourwebsite.com/placeorder');

/* STRIPE SETTINGS */
// Accept payments with Stripe?
define('stripe_enabled',true);
// Stripe Secret API Key
define('stripe_secret_key','');
// Stripe Publishable API Key
define('stripe_publish_key','');
// Stripe currency
define('stripe_currency','USD');
// Stripe IPN url, this should point to the IPN file located in the "ipn" directory
define('stripe_ipn_url','https://yourwebsite.com/ipn/stripe.php');
// PayPal cancel URl, the page the customer returns to when they cancel the payment
define('stripe_cancel_url','https://yourwebsite.com/cart');
// PayPal return URL, the page the customer returns to after the payment has been made
define('stripe_return_url','https://yourwebsite.com/placeorder');
?>
