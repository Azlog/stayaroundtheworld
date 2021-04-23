<?php
// Prevent direct access to file
defined('shoppingcart') or exit;
// Get the 4 most recent added products
$stmt = $pdo->prepare('SELECT * FROM products ORDER BY date_added DESC LIMIT 4');
$stmt->execute();
$recently_added_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?=template_header('Home')?>

<div class="featured" style="background-image:url(<?=featured_image?>)">
    <h2>Gadgets</h2>
    <p>Essential gadgets for everyday use</p>
</div>

<div class="recentlyadded content-wrapper">
    <h2>Recently Added Products</h2>
    <div class="products">
        <?php foreach ($recently_added_products as $product): ?>
        <a href="<?=url('index.php?page=product&id=' . ($product['url_structure'] ? $product['url_structure']  : $product['id']))?>" class="product">
            <?php if (!empty($product['img']) && file_exists('imgs/' . $product['img'])): ?>
            <img src="imgs/<?=$product['img']?>" width="200" height="200" alt="<?=$product['name']?>">
            <?php endif; ?>
            <span class="name"><?=$product['name']?></span>
            <span class="price">
                <?=currency_code?><?=number_format($product['price'],2)?>
                <?php if ($product['rrp'] > 0): ?>
                <span class="rrp"><?=currency_code?><?=number_format($product['rrp'],2)?></span>
                <?php endif; ?>
            </span>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<?=template_footer()?>
