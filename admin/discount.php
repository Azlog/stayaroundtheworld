<?php
defined('admin') or exit;
// Default input discount values
$discount = [
    'category_ids' => '',
    'product_ids' => '',
    'discount_code' => '',
    'discount_type' => 'Percentage',
    'discount_value' => 0,
    'start_date' => date('Y-m-d\TH:i:s'),
    'end_date' => date('Y-m-d\TH:i:s', strtotime('+1 month', strtotime(date('Y-m-d\TH:i:s')))), 
    'categories' => [],
    'products' => []
];
$types = ['Percentage', 'Fixed'];
// Get all the categories from the database
$stmt = $pdo->query('SELECT * FROM categories');
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Get all the categories from the database
$stmt = $pdo->query('SELECT * FROM products');
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (isset($_GET['id'])) {
    // ID param exists, edit an existing discount
    $page = 'Edit';
    if (isset($_POST['submit'])) {
        // Update the discount
        $stmt = $pdo->prepare('UPDATE discounts SET category_ids = ?, product_ids = ?, discount_code = ?, discount_type = ?, discount_value = ?, start_date = ?, end_date = ? WHERE id = ?');
        $stmt->execute([ $_POST['categories_list'], $_POST['products_list'], $_POST['discount_code'], $_POST['discount_type'], $_POST['discount_value'], date('Y-m-d H:i:s', strtotime($_POST['start_date'])), date('Y-m-d H:i:s', strtotime($_POST['end_date'])), $_GET['id'] ]);
        header('Location: index.php?page=discounts');
        exit;
    }
    if (isset($_POST['delete'])) {
        // Delete the discount
        $stmt = $pdo->prepare('DELETE FROM discounts WHERE id = ?');
        $stmt->execute([ $_GET['id'] ]);
        header('Location: index.php?page=discounts');
        exit;
    }
    // Get the discount from the database
    $stmt = $pdo->prepare('SELECT * FROM discounts WHERE id = ?');
    $stmt->execute([ $_GET['id'] ]);
    $discount = $stmt->fetch(PDO::FETCH_ASSOC);
    // Get the discount categories
    $stmt = $pdo->prepare('SELECT c.name, c.id FROM discounts d JOIN categories c ON FIND_IN_SET(c.id, d.category_ids) WHERE d.id = ?');
    $stmt->execute([ $_GET['id'] ]);
    $discount['categories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Get the discount products
    $stmt = $pdo->prepare('SELECT p.name, p.id FROM discounts d JOIN products p ON FIND_IN_SET(p.id, d.product_ids) WHERE d.id = ?');
    $stmt->execute([ $_GET['id'] ]);
    $discount['products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Create a new discount
    $page = 'Create';
    if (isset($_POST['submit'])) {
        $stmt = $pdo->prepare('INSERT INTO discounts (category_ids,product_ids,discount_code,discount_type,discount_value,start_date,end_date) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([ $_POST['categories_list'], $_POST['products_list'], $_POST['discount_code'], $_POST['discount_type'], $_POST['discount_value'], date('Y-m-d H:i:s', strtotime($_POST['start_date'])), date('Y-m-d H:i:s', strtotime($_POST['end_date'])) ]);
        header('Location: index.php?page=discounts');
        exit;
    }
}
?>
<?=template_admin_header($page . ' Discount', 'discounts')?>

<h2><?=$page?> Discount</h2>

<div class="content-block">

    <form action="" method="post" class="form responsive-width-100">

        <label for="code">Code</label>
        <input id="code" type="text" name="discount_code" placeholder="Code" value="<?=$discount['discount_code']?>" required>

        <label for="add_categories">Categories</label>
        <div style="display:flex;flex-flow:wrap;">
            <select name="add_categories" id="add_categories" style="width:50%;" multiple>
                <?php foreach ($categories as $cat): ?>
                <option value="<?=$cat['id']?>"><?=$cat['name']?></option>
                <?php endforeach; ?>
            </select>
            <select name="categories" style="width:50%;" multiple>
                <?php foreach ($discount['categories'] as $cat): ?>
                <option value="<?=$cat['id']?>"><?=$cat['name']?></option>
                <?php endforeach; ?>
            </select>
            <button id="add_selected_categories" style="width:50%;">Add</button>
            <button id="remove_selected_categories" style="width:50%;">Remove</button>
            <input type="hidden" name="categories_list" value="<?=implode(',', array_column($discount['categories'], 'id'))?>">
        </div>

        <label for="add_products">Products</label>
        <div style="display:flex;flex-flow:wrap;">
            <select name="add_products" id="add_products" style="width:50%;" multiple>
                <?php foreach ($products as $product): ?>
                <option value="<?=$product['id']?>"><?=$product['name']?></option>
                <?php endforeach; ?>
            </select>
            <select name="products" style="width:50%;" multiple>
                <?php foreach ($discount['products'] as $product): ?>
                <option value="<?=$product['id']?>"><?=$product['name']?></option>
                <?php endforeach; ?>
            </select>
            <button id="add_selected_products" style="width:50%;">Add</button>
            <button id="remove_selected_products" style="width:50%;">Remove</button>
            <input type="hidden" name="products_list" value="<?=implode(',', array_column($discount['products'], 'id'))?>">
        </div>

        <label for="type">Type</label>
        <select id="type" name="discount_type">
            <?php foreach ($types as $type): ?>
            <option value="<?=$type?>"<?=$discount['discount_type']==$type?' selected':''?>><?=$type?></option>
            <?php endforeach; ?>
        </select>

        <label for="discount_value">Value</label>
        <input id="discount_value" type="number" name="discount_value" placeholder="Value" min="0" step=".01" value="<?=$discount['discount_value']?>" required>

        <label for="start_date">Start Date</label>
        <input id="start_date" type="datetime-local" name="start_date" placeholder="Start Date" value="<?=date('Y-m-d\TH:i:s', strtotime($discount['start_date']))?>" required>

        <label for="end_date">End Date</label>
        <input id="end_date" type="datetime-local" name="end_date" placeholder="End Date" value="<?=date('Y-m-d\TH:i:s', strtotime($discount['end_date']))?>" required>

        <div class="submit-btns">
            <input type="submit" name="submit" value="Submit">
            <?php if ($page == 'Edit'): ?>
            <input type="submit" name="delete" value="Delete" class="delete">
            <?php endif; ?>
        </div>

    </form>

</div>

<script>
document.querySelector("#remove_selected_categories").onclick = function(e) {
    e.preventDefault();
    document.querySelectorAll("select[name='categories'] option").forEach(function(option) {
        if (option.selected) {
            let list = document.querySelector("input[name='categories_list']").value.split(",");
            list.splice(list.indexOf(option.value), 1);
            document.querySelector("input[name='categories_list']").value = list.join(",");
            option.remove();
        }
    });
};
document.querySelector("#add_selected_categories").onclick = function(e) {
    e.preventDefault();
    document.querySelectorAll("select[name='add_categories'] option").forEach(function(option) {
        if (option.selected) {
            let list = document.querySelector("input[name='categories_list']").value.split(",");
            if (!list.includes(option.value)) {
                list.push(option.value);
            }
            document.querySelector("input[name='categories_list']").value = list.join(",");
            document.querySelector("select[name='categories']").add(option.cloneNode(true));
        }
    });
};
document.querySelector("#remove_selected_products").onclick = function(e) {
    e.preventDefault();
    document.querySelectorAll("select[name='products'] option").forEach(function(option) {
        if (option.selected) {
            let list = document.querySelector("input[name='products_list']").value.split(",");
            list.splice(list.indexOf(option.value), 1);
            document.querySelector("input[name='products_list']").value = list.join(",");
            option.remove();
        }
    });
};
document.querySelector("#add_selected_products").onclick = function(e) {
    e.preventDefault();
    document.querySelectorAll("select[name='add_products'] option").forEach(function(option) {
        if (option.selected) {
            let list = document.querySelector("input[name='products_list']").value.split(",");
            if (!list.includes(option.value)) {
                list.push(option.value);
            }
            document.querySelector("input[name='products_list']").value = list.join(",");
            document.querySelector("select[name='products']").add(option.cloneNode(true));
        }
    });
};
</script>

<?=template_admin_footer()?>
