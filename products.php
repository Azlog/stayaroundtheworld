<?php
// Prevent direct access to file
defined('shoppingcart') or exit;
// Get all the categories from the database
$stmt = $pdo->query('SELECT * FROM categories');
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Get the current category from the GET request, if none exists set the default selected category to: all
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$category_sql = '';
if ($category != 'all') {
    $category_sql = 'JOIN products_categories pc ON pc.category_id = :category_id AND pc.product_id = p.id JOIN categories c ON c.id = pc.category_id';
}
// Get the sort from GET request, will occur if the user changes an item in the select box
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'sort3';
// The amounts of products to show on each page
$num_products_on_each_page = 8;
// The current page, in the URL this will appear as index.php?page=products&p=1, index.php?page=products&p=2, etc...
$current_page = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;
// Select products ordered by the date added
if ($sort == 'sort1') {
    // sort1 = Alphabetical A-Z
    $stmt = $pdo->prepare('SELECT p.* FROM products p ' . $category_sql . ' ORDER BY p.name ASC LIMIT :page,:num_products');
} elseif ($sort == 'sort2') {
    // sort2 = Alphabetical Z-A
    $stmt = $pdo->prepare('SELECT p.* FROM products p ' . $category_sql . ' ORDER BY p.name DESC LIMIT :page,:num_products');
} elseif ($sort == 'sort3') {
    // sort3 = Newest
    $stmt = $pdo->prepare('SELECT p.* FROM products p ' . $category_sql . ' ORDER BY p.date_added DESC LIMIT :page,:num_products');
} elseif ($sort == 'sort4') {
    // sort4 = Oldest
    $stmt = $pdo->prepare('SELECT p.* FROM products p ' . $category_sql . ' ORDER BY p.date_added ASC LIMIT :page,:num_products');
} elseif ($sort == 'sort5') {
    // sort5 = Highest Price
    $stmt = $pdo->prepare('SELECT p.* FROM products p ' . $category_sql . ' ORDER BY p.price DESC LIMIT :page,:num_products');
} elseif ($sort == 'sort6') {
    // sort6 = Lowest Price
    $stmt = $pdo->prepare('SELECT p.* FROM products p ' . $category_sql . ' ORDER BY p.price ASC LIMIT :page,:num_products');
} else {
    // No sort was specified, get the products with no sorting
    $stmt = $pdo->prepare('SELECT p.* FROM products p ' . $category_sql . ' LIMIT :page,:num_products');
}
// bindValue will allow us to use integer in the SQL statement, we need to use for LIMIT
if ($category != 'all') {
    $stmt->bindValue(':category_id', $category, PDO::PARAM_INT);
}
$stmt->bindValue(':page', ($current_page - 1) * $num_products_on_each_page, PDO::PARAM_INT);
$stmt->bindValue(':num_products', $num_products_on_each_page, PDO::PARAM_INT);
$stmt->execute();
// Fetch the products from the database and return the result as an Array
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Get the total number of products
$stmt = $pdo->prepare('SELECT COUNT(*) FROM products p ' . $category_sql);
if ($category != 'all') {
    $stmt->bindValue(':category_id', $category, PDO::PARAM_INT);
}
$stmt->execute();
$total_products = $stmt->fetchColumn()
?>
<?=template_header('Products')?>

<div class="products content-wrapper">

    <h1>Products</h1>

    <div class="products-header">
        <p><?=$total_products?> Products</p>
        <form action="" method="get" class="products-form">
            <input type="hidden" name="page" value="products">
            <label class="category">
                Category
                <select name="category">
                    <option value="all"<?=($category == 'all' ? ' selected' : '')?>>All</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?=$c['id']?>"<?=($category == $c['id'] ? ' selected' : '')?>><?=$c['name']?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="sortby">
                Sort by
                <select name="sort">
                    <option value="sort1"<?=($sort == 'sort1' ? ' selected' : '')?>>Alphabetical A-Z</option>
                    <option value="sort2"<?=($sort == 'sort2' ? ' selected' : '')?>>Alphabetical Z-A</option>
                    <option value="sort3"<?=($sort == 'sort3' ? ' selected' : '')?>>Newest</option>
                    <option value="sort4"<?=($sort == 'sort4' ? ' selected' : '')?>>Oldest</option>
                    <option value="sort5"<?=($sort == 'sort5' ? ' selected' : '')?>>Highest Price</option>
                    <option value="sort6"<?=($sort == 'sort6' ? ' selected' : '')?>>Lowest Price</option>
                </select>
            </label>
        </form>
    </div>

    <div class="products-wrapper">
        <?php foreach ($products as $product): ?>
        <a href="<?=url('index.php?page=product&id=' . ($product['url_structure'] ? $product['url_structure']  : $product['id']))?>" class="product">
            <?php if (!empty($product['img']) && file_exists('imgs/' . $product['img'])): ?>
            <img src="<?=base_url?>imgs/<?=$product['img']?>" width="200" height="200" alt="<?=$product['name']?>">
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

    <div class="buttons">
        <?php if ($current_page > 1): ?>
        <a href="<?=url('index.php?page=products&p=' . ($current_page-1) . '&category=' . $category . '&sort=' . $sort)?>">Prev</a>
        <?php endif; ?>
        <?php if ($total_products > ($current_page * $num_products_on_each_page) - $num_products_on_each_page + count($products)): ?>
        <a href="<?=url('index.php?page=products&p=' . ($current_page+1) . '&category=' . $category . '&sort=' . $sort)?>">Next</a>
        <?php endif; ?>
    </div>

</div>

<?=template_footer()?>
