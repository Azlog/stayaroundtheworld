<?php
// Prevent direct access to file
defined('shoppingcart') or exit;
// Default values for the input form elements
$account = [
    'first_name' => '',
    'last_name' => '',
    'address_street' => '',
    'address_city' => '',
    'address_state' => '',
    'address_zip' => '',
    'address_country' => 'United States'
];
// Error array, output errors on the form
$errors = [];
// Check if user is logged in
if (isset($_SESSION['account_loggedin'])) {
    $stmt = $pdo->prepare('SELECT * FROM accounts WHERE id = ?');
    $stmt->execute([ $_SESSION['account_id'] ]);
    // Fetch the account from the database and return the result as an Array
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
}
// Make sure when the user submits the form all data was submitted and shopping cart is not empty
if (isset($_POST['first_name'], $_POST['last_name'], $_POST['address_street'], $_POST['address_city'], $_POST['address_state'], $_POST['address_zip'], $_POST['address_country'], $_SESSION['cart'])) {
    $account_id = null;
    // If the user is already logged in
    if (isset($_SESSION['account_loggedin'])) {
        // Account logged-in, update the user's details
        $stmt = $pdo->prepare('UPDATE accounts SET first_name = ?, last_name = ?, address_street = ?, address_city = ?, address_state = ?, address_zip = ?, address_country = ? WHERE id = ?');
        $stmt->execute([ $_POST['first_name'], $_POST['last_name'], $_POST['address_street'], $_POST['address_city'], $_POST['address_state'], $_POST['address_zip'], $_POST['address_country'], $_SESSION['account_id'] ]);
        $account_id = $_SESSION['account_id'];
    } else if (isset($_POST['email'], $_POST['password'], $_POST['cpassword']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        // User is not logged in, check if the account already exists with the email they submitted
        $stmt = $pdo->prepare('SELECT id FROM accounts WHERE email = ?');
        $stmt->execute([ $_POST['email'] ]);
    	if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            // Email exists, user should login instead...
    		$errors[] = 'Account already exists with this email, please login instead!';
        }
        if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
            // Password must be between 5 and 20 characters long.
            $errors[] = 'Password must be between 5 and 20 characters long!';
    	}
        if ($_POST['password'] != $_POST['cpassword']) {
            // Password and confirm password fields do not match...
            $errors[] = 'Passwords do not match!';
        }
        if (!$errors) {
            // Email doesnt exist, create new account
            $stmt = $pdo->prepare('INSERT INTO accounts (email, password, first_name, last_name, address_street, address_city, address_state, address_zip, address_country) VALUES (?,?,?,?,?,?,?,?,?)');
            // Hash the password
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt->execute([ $_POST['email'], $password, $_POST['first_name'], $_POST['last_name'], $_POST['address_street'], $_POST['address_city'], $_POST['address_state'], $_POST['address_zip'], $_POST['address_country'] ]);
            $account_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare('SELECT * FROM accounts WHERE id = ?');
            $stmt->execute([ $account_id ]);
            // Fetch the account from the database and return the result as an Array
            $account = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } else if (account_required) {
        $errors[] = 'Account creation required!';
    }
    if (!$errors) {
        // No errors, process the order
        $products_in_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        $subtotal = 0.00;
        $shippingtotal = 0.00;
        $selected_shipping_method = isset($_SESSION['shipping_method']) ? $_SESSION['shipping_method'] : null;
        // If there are products in cart
        if ($products_in_cart) {
            // There are products in the cart so we need to select those products from the database
            // Products in cart array to question mark string array, we need the SQL statement to include: IN (?,?,?,...etc)
            $array_to_question_marks = implode(',', array_fill(0, count($products_in_cart), '?'));
            $stmt = $pdo->prepare('SELECT p.id, c.id AS category_id, p.* FROM products p LEFT JOIN products_categories pc ON p.id = pc.product_id LEFT JOIN categories c ON c.id = pc.category_id WHERE p.id IN (' . $array_to_question_marks . ') GROUP BY p.id, c.id');
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
                                $cart_product['shipping_price'] = (float)$shipping_method['price'] * (int)$cart_product['quantity'];
                                $shippingtotal += $cart_product['shipping_price'];
                            }
                        }
                        // Calculate the discount
                        if (isset($discount) && $discount && $current_date >= strtotime($discount['start_date']) && $current_date <= strtotime($discount['end_date'])) {
                            if ((!$discount['category_ids'] && !$discount['product_ids']) || in_array($product['id'], explode(',', $discount['product_ids'])) || in_array($product['category_id'], explode(',', $discount['category_ids']))) {
                                $cart_product['discounted'] = true;
                            }
                        }
                    }
                }
            }
            // Number of discounted products
            $num_discounted_products = count(array_column($products_in_cart, 'discounted'));
            // Iterate the products and update the price for the discounted products
            foreach ($products_in_cart as &$cart_product) {
                if (isset($cart_product['discounted']) && $cart_product['discounted']) {
                    if ($cart_product['options_price'] > 0) {
                        $price = &$cart_product['options_price'];
                    } else {
                        $price = &$cart_product['meta']['price'];
                    }
                    if ($discount['discount_type'] == 'Percentage') {
                        $price -= round((float)$price * ((float)$discount['discount_value']/100), 2);
                    }
                    if ($discount['discount_type'] == 'Fixed') {
                        $price -= round((float)$discount['discount_value'] / $num_discounted_products, 2);
                    }
                }
            }
        }
        // Process Stripe Payment
        if (isset($_POST['stripe']) && $products_in_cart) {
            // Include the stripe lib
            require_once('lib/stripe/init.php');
            $stripe = new \Stripe\StripeClient(stripe_secret_key);
            $line_items = [];
            // Iterate the products in cart and add each product to the array above
            for ($i = 0; $i < count($products_in_cart); $i++) {
                $line_items[] = [
                    'quantity' => $products_in_cart[$i]['quantity'],
                    'price_data' => [
                        'currency' => stripe_currency,
                        'unit_amount' => ($products_in_cart[$i]['options_price'] > 0 ? $products_in_cart[$i]['options_price'] : $products_in_cart[$i]['meta']['price'])*100,
                        'product_data' => [
                            'name' => $products_in_cart[$i]['meta']['name'],
                            'metadata' => [
                                'item_id' => $products_in_cart[$i]['id'],
                                'item_options' => $products_in_cart[$i]['options'],
                                'item_shipping' => $products_in_cart[$i]['shipping_price']
                            ]
                        ]
                    ]
                ];
            }
            // Add the shipping
            $line_items[] = [
                'quantity' => 1,
                'price_data' => [
                    'currency' => stripe_currency,
                    'unit_amount' => $shippingtotal*100,
                    'product_data' => [
                        'name' => 'Shipping',
                        'description' => $selected_shipping_method,
                        'metadata' => [
                            'item_id' => 'shipping'
                        ]
                    ]
                ]
            ];
            // Webhook that will notify the stripe IPN file when a payment has been made
            $webhooks = $stripe->webhookEndpoints->all();
            $webhook = null;
            $secret = '';
            foreach ($webhooks as $wh) {
                if ($wh['description'] == 'codeshack_shoppingcart_system') {
                    $webhook = $wh;
                    $secret = $webhook['metadata']['secret'];
                }
            }
            if ($webhook == null) {
                $webhook = $stripe->webhookEndpoints->create([
                    'url' => stripe_ipn_url,
                    'description' => 'codeshack_shoppingcart_system',
                    'enabled_events' => ['checkout.session.completed'],
                    'metadata' => ['secret' => '']
                ]);
                $secret = $webhook['secret'];
                $stripe->webhookEndpoints->update($webhook['id'], ['metadata' => ['secret' => $secret] ]);
            }
            $stripe->webhookEndpoints->update($webhook['id'], ['url' => stripe_ipn_url . '?key=' . $secret]);
            // Create the stripe checkout session and redirect the user
            $session = $stripe->checkout->sessions->create([
                'success_url' => stripe_return_url,
                'cancel_url' => stripe_cancel_url,
                'payment_method_types' => ['card'],
                'line_items' => $line_items,
                'mode' => 'payment',
                'customer_email' => isset($account['email']) && !empty($account['email']) ? $account['email'] : $_POST['email'],
                'metadata' => [
                    'first_name' => $_POST['first_name'],
                    'last_name' => $_POST['last_name'],
                    'address_street' => $_POST['address_street'],
                    'address_city' => $_POST['address_city'],
                    'address_state' => $_POST['address_state'],
                    'address_zip' => $_POST['address_zip'],
                    'address_country' => $_POST['address_country'],
                    'account_id' => $account_id
                ]
            ]);
            header('Location: stripe-redirect.php?stripe_session_id=' . $session['id']);
            exit;
        }
        // Process PayPal Payment
        if (isset($_POST['paypal']) && $products_in_cart) {
            // Process PayPal Checkout
            // Variable that will stored all details for all products in the shopping cart
            $data = [];
            // Add all the products that are in the shopping cart to the data array variable
            for ($i = 0; $i < count($products_in_cart); $i++) {
                $data['item_number_' . ($i+1)] = $products_in_cart[$i]['id'];
                $data['item_name_' . ($i+1)] = str_replace(['(', ')'], '', $products_in_cart[$i]['meta']['name']);
                $data['quantity_' . ($i+1)] = $products_in_cart[$i]['quantity'];
                $data['amount_' . ($i+1)] = $products_in_cart[$i]['options_price'] > 0 ? $products_in_cart[$i]['options_price'] : $products_in_cart[$i]['meta']['price'];
                $data['on0_' . ($i+1)] = 'Options';
                $data['os0_' . ($i+1)] = $products_in_cart[$i]['options'];
                $data['shipping_' . ($i+1)] = $products_in_cart[$i]['shipping_price'];
            }
            // Variables we need to pass to paypal
            $data = $data + [
                'cmd'			=> '_cart',
                'upload'        => '1',
                'custom'        => $account_id,
                'business' 		=> paypal_email,
                'cancel_return'	=> paypal_cancel_url,
                'notify_url'	=> paypal_ipn_url,
                'currency_code'	=> paypal_currency,
                'return'        => paypal_return_url
            ];
            if ($account_id != null) {
                // Log the user in with the details provided
                session_regenerate_id();
                $_SESSION['account_loggedin'] = TRUE;
                $_SESSION['account_id'] = $account_id;
                $_SESSION['account_admin'] = $account ? $account['admin'] : 0;
            }
            // Redirect the user to the PayPal checkout screen
            header('location:' . (paypal_testmode ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr') . '?' . http_build_query($data));
            // End the script, don't need to execute anything else
            exit;
        }
        if (isset($_POST['checkout']) && $products_in_cart) {
            // Process Normal Checkout
            // Iterate each product in the user's shopping cart
            // Unique transaction ID
            $transaction_id = strtoupper(uniqid('SC') . substr(md5(mt_rand()), 0, 5));
            $stmt = $pdo->prepare('INSERT INTO transactions (txn_id, payment_amount, payment_status, created, payer_email, first_name, last_name, address_street, address_city, address_state, address_zip, address_country, account_id, payment_method) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([
                $transaction_id,
                $subtotal+$shippingtotal,
                'Completed',
                date('Y-m-d H:i:s'),
                isset($account['email']) && !empty($account['email']) ? $account['email'] : $_POST['email'],
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['address_street'],
                $_POST['address_city'],
                $_POST['address_state'],
                $_POST['address_zip'],
                $_POST['address_country'],
                $account_id,
                'website'
            ]);
            $order_id = $pdo->lastInsertId();
            foreach ($products_in_cart as $product) {
                // For every product in the shopping cart insert a new transaction into our database
                $stmt = $pdo->prepare('INSERT INTO transactions_items (txn_id, item_id, item_price, item_quantity, item_options, item_shipping_price) VALUES (?,?,?,?,?,?)');
                $stmt->execute([ $transaction_id, $product['id'], $product['options_price'] > 0 ? $product['options_price'] : $product['meta']['price'], $product['quantity'], $product['options'], $product['shipping_price'] ]);
                // Update product quantity in the products table
                $stmt = $pdo->prepare('UPDATE products SET quantity = quantity - ? WHERE quantity > 0 AND id = ?');
                $stmt->execute([ $product['quantity'], $product['id'] ]);
            }
            if ($account_id != null) {
                // Log the user in with the details provided
                session_regenerate_id();
                $_SESSION['account_loggedin'] = TRUE;
                $_SESSION['account_id'] = $account_id;
                $_SESSION['account_admin'] = $account ? $account['admin'] : 0;
            }
            send_order_details_email(
                isset($account['email']) && !empty($account['email']) ? $account['email'] : $_POST['email'],
                $products_in_cart,
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['address_street'],
                $_POST['address_city'],
                $_POST['address_state'],
                $_POST['address_zip'],
                $_POST['address_country'],
                $subtotal+$shippingtotal,
                $order_id
            );
            header('Location: ' . url('index.php?page=placeorder'));
            exit;
        }
    }
    // Preserve form details if the user encounters an error
    $account = [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'address_street' => $_POST['address_street'],
        'address_city' => $_POST['address_city'],
        'address_state' => $_POST['address_state'],
        'address_zip' => $_POST['address_zip'],
        'address_country' => $_POST['address_country']
    ];
}
// Redirect the user if the shopping cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: ' . url('index.php?page=cart'));
    exit;
}
// List of countries available, feel free to remove any country from the array
$countries = ["Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe"];

?>
<?=template_header('Checkout')?>

<div class="checkout content-wrapper">

    <h1>Checkout</h1>

    <p class="error"><?=implode('<br>', $errors)?></p>

    <?php if (!isset($_SESSION['account_loggedin'])): ?>
    <p>Already have an account? <a href="<?=url('index.php?page=myaccount')?>">Log In</a></p>
    <?php endif; ?>

    <form action="" method="post">

        <?php if (!isset($_SESSION['account_loggedin'])): ?>
        <h2>Create Account<?php if (!account_required): ?> (optional)<?php endif; ?></h2>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="john@example.com">

        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Password">

        <label for="cpassword">Confirm Password</label>
        <input type="password" name="cpassword" id="cpassword" placeholder="Confirm Password">
        <?php endif; ?>

        <h2>Shipping Details</h2>

        <div class="row1">
            <label for="first_name">First Name</label>
            <input type="text" value="<?=$account['first_name']?>" name="first_name" id="first_name" placeholder="John" required>
        </div>

        <div class="row2">
            <label for="last_name">Last Name</label>
            <input type="text" value="<?=$account['last_name']?>" name="last_name" id="last_name" placeholder="Doe" required>
        </div>

        <label for="address_street">Address</label>
        <input type="text" value="<?=$account['address_street']?>" name="address_street" id="address_street" placeholder="24 High Street" required>

        <label for="address_city">City</label>
        <input type="text" value="<?=$account['address_city']?>" name="address_city" id="address_city" placeholder="New York" required>

        <div class="row1">
            <label for="address_state">State</label>
            <input type="text" value="<?=$account['address_state']?>" name="address_state" id="address_state" placeholder="NY" required>
        </div>

        <div class="row2">
            <label for="address_zip">Zip</label>
            <input type="text" value="<?=$account['address_zip']?>" name="address_zip" id="address_zip" placeholder="10001" required>
        </div>

        <label for="address_country">Country</label>
        <select name="address_country" required>
            <?php foreach($countries as $country): ?>
            <option value="<?=$country?>"<?=$country==$account['address_country']?' selected':''?>><?=$country?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="checkout">Place Order</button>

        <?php if (stripe_enabled): ?>
        <div class="stripe">
            <button type="submit" name="stripe">Pay with Stripe Checkout</button>
        </div>
        <?php endif; ?>

        <?php if (paypal_enabled): ?>
        <div class="paypal">
            <button type="submit" name="paypal"><img src="https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-100px.png" alt="PayPal Logo"></button>
        </div>
        <?php endif; ?>

    </form>

</div>

<?=template_footer()?>
