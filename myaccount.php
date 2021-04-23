<?php
// Prevent direct access to file
defined('shoppingcart') or exit;
// User clicked the "Login" button, proceed with the login process... check POST data and validate email
if (isset($_POST['login'], $_POST['email'], $_POST['password']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    // Check if the account exists
    $stmt = $pdo->prepare('SELECT * FROM accounts WHERE email = ?');
    $stmt->execute([ $_POST['email'] ]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    // If account exists verify password
    if ($account && password_verify($_POST['password'], $account['password'])) {
        // User has logged in, create session data
        session_regenerate_id();
        $_SESSION['account_loggedin'] = TRUE;
        $_SESSION['account_id'] = $account['id'];
        $_SESSION['account_admin'] = $account['admin'];
        $products_in_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        if ($products_in_cart) {
            // user has products in cart, redirect them to the checkout page
            header('Location: ' . url('index.php?page=checkout'));
        } else {
            // Redirect the user back to the same page, they can then see their order history
            header('Location: ' . url('index.php?page=myaccount'));
        }
        exit;
    } else {
        $error = 'Incorrect Email/Password!';
    }
}
// Variable that will output registration errors
$register_error = '';
// User clicked the "Register" button, proceed with the registration process... check POST data and validate email
if (isset($_POST['register'], $_POST['email'], $_POST['password'], $_POST['cpassword']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    // Check if the account exists
    $stmt = $pdo->prepare('SELECT * FROM accounts WHERE email = ?');
    $stmt->execute([ $_POST['email'] ]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($account) {
        // Account exists!
        $register_error = 'Account already exists with this email!';
    } else if ($_POST['cpassword'] != $_POST['password']) {
        $register_error = 'Passwords do not match!';
    } else if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
        // Password must be between 5 and 20 characters long.
        $register_error = 'Password must be between 5 and 20 characters long!';
    } else {
        // Account doesnt exist, create new account
        $stmt = $pdo->prepare('INSERT INTO accounts (email, password, first_name, last_name, address_street, address_city, address_state, address_zip, address_country) VALUES (?,?,"","","","","","","")');
        // Hash the password
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt->execute([ $_POST['email'], $password ]);
        $account_id = $pdo->lastInsertId();
        // Automatically login the user
        session_regenerate_id();
        $_SESSION['account_loggedin'] = TRUE;
        $_SESSION['account_id'] = $account_id;
        $_SESSION['account_admin'] = 0;
        $products_in_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
        if ($products_in_cart) {
            // User has products in cart, redirect them to the checkout page
            header('Location: ' . url('index.php?page=checkout'));
        } else {
            // Redirect the user back to the same page, they can then see their order history
            header('Location: ' . url('index.php?page=myaccount'));
        }
        exit;
    }
}
// If user is logged in
if (isset($_SESSION['account_loggedin'])) {
    // Select all the users transations, this will appear under "My Orders"
    $stmt = $pdo->prepare('SELECT
        p.img,
        p.name,
        p.download_url,
        t.txn_id,
        t.created AS transaction_date,
        ti.item_price AS price,
        ti.item_quantity AS quantity,
        ti.item_shipping_price
        FROM transactions t
        JOIN transactions_items ti ON ti.txn_id = t.txn_id
        JOIN accounts a ON a.id = t.account_id
        JOIN products p ON p.id = ti.item_id
        WHERE t.account_id = ?
        ORDER BY t.created DESC');
    $stmt->execute([ $_SESSION['account_id'] ]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<?=template_header('My Account')?>

<div class="myaccount content-wrapper">

    <?php if (!isset($_SESSION['account_loggedin'])): ?>

    <div class="login-register">

        <div class="login">

            <h1>Login</h1>

            <form action="" method="post">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="john@example.com" required>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <input name="login" type="submit" value="Login">
            </form>

            <?php if ($error): ?>
            <p class="error"><?=$error?></p>
            <?php endif; ?>

        </div>

        <div class="register">

            <h1>Register</h1>

            <form action="" method="post">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="john@example.com" required>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <label for="cpassword">Confirm Password</label>
                <input type="password" name="cpassword" id="cpassword" placeholder="Confirm Password" required>
                <input name="register" type="submit" value="Register">
            </form>

            <?php if ($register_error): ?>
            <p class="error"><?=$register_error?></p>
            <?php endif; ?>

        </div>

    </div>

    <?php else: ?>

    <h1>My Account</h1>

    <h2>My Orders</h2>

    <table>
        <thead>
            <tr>
                <td colspan="2">Product</td>
                <td class="rhide">Date</td>
                <td class="rhide">Price</td>
                <td class="rhide">Shipping</td>
                <td>Quantity</td>
                <td>Total</td>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($transactions)): ?>
            <tr>
                <td colspan="7" style="text-align:center;">You have no recent orders</td>
            </tr>
            <?php else: ?>
            <?php foreach ($transactions as $transaction): ?>
            <tr>
                <td class="img">
                    <?php if (!empty($transaction['img']) && file_exists('imgs/' . $transaction['img'])): ?>
                    <img src="<?=base_url?>imgs/<?=$transaction['img']?>" width="50" height="50" alt="<?=$transaction['name']?>">
                    <?php endif; ?>
                </td>
                <td>
                    <?=$transaction['name']?>
                    <?php if ($transaction['download_url']): ?>
                    <br>
                    <a href="<?=url('index.php?page=download&id=' . md5($transaction['txn_id']))?>" download>Download</a>
                    <?php endif; ?>
                </td>
                <td class="rhide"><?=$transaction['transaction_date']?></td>
                <td class="price rhide"><?=currency_code?><?=number_format($transaction['price'],2)?></td>
                <td class="price rhide"><?=currency_code?><?=number_format($transaction['item_shipping_price'],2)?></td>
                <td class="quantity"><?=$transaction['quantity']?></td>
                <td class="price"><?=currency_code?><?=number_format($transaction['price'] * $transaction['quantity'] + $transaction['item_shipping_price'],2)?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php endif; ?>

</div>

<?=template_footer()?>
