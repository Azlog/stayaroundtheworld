<?php
defined('admin') or exit;
// SQL query to get all shipping methods from the "shipping" table
$stmt = $pdo->prepare('SELECT * FROM shipping');
$stmt->execute();
$shipping = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?=template_admin_header('Shipping', 'shipping')?>

<h2>Shipping</h2>

<div class="links">
    <a href="index.php?page=shipping_process">Create Shipping Method</a>
</div>

<div class="content-block">
    <div class="table">
        <table>
            <thead>
                <tr>
                    <td>#</td>
                    <td>Name</td>
                    <td>Product Price Range</td>
                    <td>Product Weight Range</td>
                    <td>Total Shipping Price</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($shipping)): ?>
                <tr>
                    <td colspan="8" style="text-align:center;">There are no shipping methods</td>
                </tr>
                <?php else: ?>
                <?php foreach ($shipping as $s): ?>
                <tr class="details" onclick="location.href='index.php?page=shipping_process&id=<?=$s['id']?>'">
                    <td><?=$s['id']?></td>
                    <td><?=$s['name']?></td>
                    <td><?=currency_code?><?=number_format($s['price_from'], 2)?>-<?=currency_code?><?=number_format($s['price_to'], 2)?></td>
                    <td><?=number_format($s['weight_from'], 2)?> lbs-<?=number_format($s['weight_to'], 2)?> lbs</td>
                    <td><?=currency_code?><?=number_format($s['price'], 2)?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?=template_admin_footer()?>
