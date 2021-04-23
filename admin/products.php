<?php
defined('admin') or exit;
// SQL query to get all products from the "products" table
$stmt = $pdo->prepare('SELECT p.*, GROUP_CONCAT(pi.img) AS imgs FROM products p LEFT JOIN products_images pi ON p.id = pi.product_id GROUP BY p.id ORDER BY p.date_added ASC');
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?=template_admin_header('Products', 'products')?>

<h2>Products</h2>

<div class="links">
    <a href="index.php?page=product">Create Product</a>
</div>

<div class="content-block">
    <div class="table">
        <table>
            <thead>
                <tr>
                    <td class="responsive-hidden">#</td>
                    <td>Name</td>
                    <td>Price</td>
                    <td class="responsive-hidden">RRP</td>
                    <td>Quantity</td>
                    <td class="responsive-hidden">Images</td>
                    <td class="responsive-hidden">Created</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="8" style="text-align:center;">There are no products</td>
                </tr>
                <?php else: ?>
                <?php foreach ($products as $product): ?>
                <tr class="details" onclick="location.href='index.php?page=product&id=<?=$product['id']?>'">
                    <td class="responsive-hidden"><?=$product['id']?></td>
                    <td><?=$product['name']?></td>
                    <td><?=currency_code?><?=number_format($product['price'], 2)?></td>
                    <td class="responsive-hidden"><?=currency_code?><?=number_format($product['rrp'], 2)?></td>
                    <td><?=$product['quantity']?></td>
                    <td class="responsive-hidden">
                        <?php foreach (explode(',',$product['imgs']) as $img): ?>
                        <?php if ($img): ?>
                        <img src="../imgs/<?=$img?>" width="32" height="32" alt="<?=$img?>">
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </td>
                    <td class="responsive-hidden"><?=date('F j, Y', strtotime($product['date_added']))?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?=template_admin_footer()?>
