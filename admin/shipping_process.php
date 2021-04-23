<?php
defined('admin') or exit;
// Default input shipping values
$shipping = [
    'name' => '',
    'price_from' => '',
    'price_to' => '',
    'weight_from' => '',
    'weight_to' => '',
    'price' => ''
];
if (isset($_GET['id'])) {
    // ID param exists, edit an existing shipping method
    $page = 'Edit';
    if (isset($_POST['submit'])) {
        // Update the shipping method
        $stmt = $pdo->prepare('UPDATE shipping SET name = ?, price_from = ?, price_to = ?, weight_from = ?, weight_to = ?, price = ? WHERE id = ?');
        $stmt->execute([ $_POST['name'], $_POST['price_from'], $_POST['price_to'], $_POST['weight_from'], $_POST['weight_to'], $_POST['price'], $_GET['id'] ]);
        header('Location: index.php?page=shipping');
        exit;
    }
    if (isset($_POST['delete'])) {
        // Delete the shipping method
        $stmt = $pdo->prepare('DELETE FROM shipping WHERE id = ?');
        $stmt->execute([ $_GET['id'] ]);
        header('Location: index.php?page=shipping');
        exit;
    }
    // Get the shipping method from the database
    $stmt = $pdo->prepare('SELECT * FROM shipping WHERE id = ?');
    $stmt->execute([ $_GET['id'] ]);
    $shipping = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    // Create a new shipping method
    $page = 'Create';
    if (isset($_POST['submit'])) {
        $stmt = $pdo->prepare('INSERT INTO shipping (name, price_from, price_to, weight_from, weight_to, price) VALUES (?,?,?,?,?,?)');
        $stmt->execute([ $_POST['name'], $_POST['price_from'], $_POST['price_to'], $_POST['weight_from'], $_POST['weight_to'], $_POST['price'] ]);
        header('Location: index.php?page=shipping');
        exit;
    }
}
?>
<?=template_admin_header($page . ' Shipping Method', 'shipping')?>

<h2><?=$page?> Shipping Method</h2>

<div class="content-block">

    <form action="" method="post" class="form responsive-width-100">

        <label for="name">Name</label>
        <input type="text" name="name" placeholder="Name" value="<?=$shipping['name']?>" required>

        <label for="price">Product Price Range</label>
        <div style="display:flex;margin:0;">
            <input type="number" name="price_from" placeholder="From" min="0" step=".01" value="<?=$shipping['price_from']?>" required>
            <span style="padding-top:15px">&nbsp;&nbsp;&nbsp;&mdash;&nbsp;&nbsp;&nbsp;</span>
            <input type="number" name="price_to" placeholder="To" min="0" step=".01" value="<?=$shipping['price_to']?>" required>
        </div>

        <label for="price">Product Weight Range (lbs)</label>
        <div style="display:flex;margin:0;">
            <input type="number" name="weight_from" placeholder="From" min="0" step=".01" value="<?=$shipping['weight_from']?>" required>
            <span style="padding-top:15px">&nbsp;&nbsp;&nbsp;&mdash;&nbsp;&nbsp;&nbsp;</span>
            <input type="number" name="weight_to" placeholder="To" min="0" step=".01" value="<?=$shipping['weight_to']?>" required>
        </div>

        <label for="name">Total Shipping Price</label>
        <input type="number" name="price" placeholder="3.99" min="0" step=".01" value="<?=$shipping['price']?>" required>

        <div class="submit-btns">
            <input type="submit" name="submit" value="Submit">
            <?php if ($page == 'Edit'): ?>
            <input type="submit" name="delete" value="Delete" class="delete">
            <?php endif; ?>
        </div>

    </form>

</div>

<?=template_admin_footer()?>
