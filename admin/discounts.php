<?php
defined('admin') or exit;
// SQL query to get all discounts from the "discounts" table
$stmt = $pdo->prepare('SELECT d.*, GROUP_CONCAT(DISTINCT p.name) product_names, GROUP_CONCAT(DISTINCT c.name) category_names FROM discounts d LEFT JOIN products p ON FIND_IN_SET(p.id, d.product_ids) LEFT JOIN categories c ON FIND_IN_SET(c.id, d.category_ids) GROUP BY d.id');
$stmt->execute();
$discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Get the current date
$current_date = strtotime((new DateTime())->format('Y-m-d H:i:s'));
?>
<?=template_admin_header('Discounts', 'discounts')?>

<h2>Discounts</h2>

<div class="links">
    <a href="index.php?page=discount">Create Discount</a>
</div>

<div class="content-block">
    <div class="table">
        <table>
            <thead>
                <tr>
                    <td class="responsive-hidden">#</td>
                    <td>Code</td>
                    <td>Active</td>
                    <td class="responsive-hidden">Categories</td>
                    <td class="responsive-hidden">Products</td>
                    <td>Type</td>
                    <td>Value</td>
                    <td class="responsive-hidden">Start Date</td>
                    <td class="responsive-hidden">End Date</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($discounts)): ?>
                <tr>
                    <td colspan="8" style="text-align:center;">There are no discounts</td>
                </tr>
                <?php else: ?>
                <?php foreach ($discounts as $discount): ?>
                <tr class="details" onclick="location.href='index.php?page=discount&id=<?=$discount['id']?>'">
                    <td class="responsive-hidden"><?=$discount['id']?></td>
                    <td><?=$discount['discount_code']?></td>
                    <td><?=$current_date >= strtotime($discount['start_date']) && $current_date <= strtotime($discount['end_date']) ? 'Yes' : 'No'?></td>
                    <td class="responsive-hidden"><?=$discount['category_names'] ? $discount['category_names'] : 'all'?></td>
                    <td class="responsive-hidden"><?=$discount['product_names'] ? $discount['product_names'] : 'all'?></td>
                    <td><?=$discount['discount_type']?></td>
                    <td><?=$discount['discount_value']?></td>
                    <td class="responsive-hidden"><?=$discount['start_date']?></td>
                    <td class="responsive-hidden"><?=$discount['end_date']?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?=template_admin_footer()?>
