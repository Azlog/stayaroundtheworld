<?php
defined('admin') or exit;

// SQL query that will get all orders and sort by the date created
$stmt = $pdo->prepare('SELECT
    p.img AS img,
    p.name AS name,
    t.*,
    ti.item_price AS price,
    ti.item_quantity AS quantity,
    ti.item_options AS options
    FROM transactions t
    JOIN transactions_items ti ON ti.txn_id = t.txn_id
    JOIN products p ON p.id = ti.item_id
    ORDER BY t.created DESC');
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?=template_admin_header('Orders', 'orders')?>

<h2>Orders</h2>

<div class="content-block">
    <div class="table">
        <table>
            <thead>
                <tr>
                    <td colspan="2">Product</td>
                    <td class="responsive-hidden">Date</td>
                    <td class="responsive-hidden">Price</td>
                    <td>Quantity</td>
                    <td>Total</td>
                    <td class="responsive-hidden">Email</td>
                    <td class="responsive-hidden">Status</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="8" style="text-align:center;">There are no recent orders</td>
                </tr>
                <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <tr class="details">
                    <td class="img">
                        <?php if (!empty($order['img']) && file_exists('../imgs/' . $order['img'])): ?>
                        <img src="../imgs/<?=$order['img']?>" width="32" height="32" alt="<?=$order['name']?>">
                        <?php endif; ?>
                    </td>
                    <td><?=$order['name']?></td>
                    <td class="responsive-hidden"><?=date('F j, Y', strtotime($order['created']))?></td>
                    <td class="responsive-hidden"><?=currency_code?><?=number_format($order['price'],2)?></td>
                    <td><?=$order['quantity']?></td>
                    <td><?=currency_code?><?=number_format($order['price'] * $order['quantity'], 2)?></td>
                    <td class="responsive-hidden"><?=$order['payer_email']?></td>
                    <td class="responsive-hidden"><?=$order['payment_status']?></td>
                </tr>
                <tr class="expanded-details">
                    <td colspan="8">
                        <div>
                            <div>
                                <span>Transaction ID</span>
                                <span><?=$order['txn_id']?></span>
                            </div>
                            <div>
                                <span>Payment Method</span>
                                <span><?=$order['payment_method']?></span>
                            </div>
                            <div>
                                <span>Created</span>
                                <span><?=$order['created']?></span>
                            </div>
                            <div>
                                <span>Name</span>
                                <span><?=$order['first_name']?> <?=$order['last_name']?></span>
                            </div>
                            <div>
                                <span>Account ID</span>
                                <span><?=$order['account_id']?></span>
                            </div>
                            <div>
                                <span>Email</span>
                                <span><?=$order['payer_email']?></span>
                            </div>
                            <div>
                                <span>Status</span>
                                <span><?=$order['payment_status']?></span>
                            </div>
                            <div>
                                <span>Address</span>
                                <span>
                                    <?=$order['address_street']?><br>
                                    <?=$order['address_city']?><br>
                                    <?=$order['address_state']?><br>
                                    <?=$order['address_zip']?><br>
                                    <?=$order['address_country']?>
                                </span>
                            </div>
                            <div>
                                <span>Options</span>
                                <span><?=$order['options']?></span>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <script>
        document.querySelectorAll(".admin .details").forEach(function(detail) {
            detail.onclick = function() {
                let display = this.nextElementSibling.style.display == 'table-row' ? 'none' : 'table-row';
                this.nextElementSibling.style.display = display;
            };
        });
        </script>
    </div>
</div>

<?=template_admin_footer()?>
