<?php
defined('admin') or exit;
// Default account product values
$account = [
    'email' => '',
    'password' => '',
    'first_name' => '',
    'last_name' => '',
    'address_street' => '',
    'address_city' => '',
    'address_state' => '',
    'address_zip' => '',
    'address_country' => '',
    'admin' => 'No'
];
if (isset($_GET['id'])) {
    // Retrieve the account from the database
    $stmt = $pdo->prepare('SELECT * FROM accounts WHERE id = ?');
    $stmt->execute([ $_GET['id'] ]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    // ID param exists, edit an existing account
    $page = 'Edit';
    if (isset($_POST['submit'])) {
        // Update the account
        $stmt = $pdo->prepare('UPDATE accounts SET email = ?, password = ?, first_name = ?, last_name = ?, address_street = ?, address_city = ?, address_state = ?, address_zip = ?, address_country = ?, admin = ? WHERE id = ?');
        $password = $_POST['password'] == $account['password'] ? $_POST['password'] : password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt->execute([ $_POST['email'], $password, $_POST['first_name'], $_POST['last_name'], $_POST['address_street'], $_POST['address_city'], $_POST['address_state'], $_POST['address_zip'], $_POST['address_country'], $_POST['admin'], $_GET['id'] ]);
        header('Location: index.php?page=accounts');
        exit;
    }
    if (isset($_POST['delete'])) {
        // Delete the account
        $stmt = $pdo->prepare('DELETE FROM accounts WHERE id = ?');
        $stmt->execute([ $_GET['id'] ]);
        header('Location: index.php?page=accounts');
        exit;
    }
} else {
    // Create a new account
    $page = 'Create';
    if (isset($_POST['submit'])) {
        $stmt = $pdo->prepare('INSERT INTO accounts (email,password,first_name,last_name,address_street,address_city,address_state,address_zip,address_country,admin) VALUES (?,?,?,?,?,?,?,?,?,?)');
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt->execute([ $_POST['email'], $password, $_POST['first_name'], $_POST['last_name'], $_POST['address_street'], $_POST['address_city'], $_POST['address_state'], $_POST['address_zip'], $_POST['address_country'], $_POST['admin'] ]);
        header('Location: index.php?page=accounts');
        exit;
    }
}
// List of countries available, feel free to remove any country from the array
$countries = ["Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe"];
?>
<?=template_admin_header($page . ' Account', 'accounts')?>

<h2><?=$page?> Account</h2>

<div class="content-block">

    <form action="" method="post" class="form responsive-width-100">

        <label for="email">Email</label>
        <input id="email" type="email" name="email" placeholder="Email" value="<?=$account['email']?>" required>

        <label for="password">Password</label>
        <input id="password" type="password" name="password" placeholder="Password" value="<?=$account['password']?>" required>

        <label for="first_name">First Name</label>
        <input id="first_name" type="text" name="first_name" placeholder="Joe" value="<?=$account['first_name']?>">

        <label for="last_name">Last Name</label>
        <input id="last_name" type="text" name="last_name" placeholder="Bloggs" value="<?=$account['last_name']?>">

        <label for="admin">Admin</label>
        <select id="admin" name="admin" required>
            <option value="0"<?=$account['admin']==0?' selected':''?>>No</option>
            <option value="1"<?=$account['admin']==1?' selected':''?>>Yes</option>
        </select>

        <label for="address_street">Address Street</label>
        <input id="address_street" type="text" name="address_street" placeholder="24 High Street" value="<?=$account['address_street']?>">

        <label for="address_city">Address City</label>
        <input id="address_city" type="text" name="address_city" placeholder="New York" value="<?=$account['address_city']?>">

        <label for="address_state">Address State</label>
        <input id="address_state" type="text" name="address_state" placeholder="NY" value="<?=$account['address_state']?>">

        <label for="address_zip">Address Zip</label>
        <input id="address_zip" type="text" name="address_zip" placeholder="10001" value="<?=$account['address_zip']?>">

        <label for="address_country">Country</label>
        <select id="address_country" name="address_country" required>
            <?php foreach($countries as $country): ?>
            <option value="<?=$country?>"<?=$country==$account['address_country']?' selected':''?>><?=$country?></option>
            <?php endforeach; ?>
        </select>
        <br>

        <div class="submit-btns">
            <input type="submit" name="submit" value="Submit">
            <?php if ($page == 'Edit'): ?>
            <input type="submit" name="delete" value="Delete" class="delete">
            <?php endif; ?>
        </div>

    </form>

</div>

<?=template_admin_footer()?>
