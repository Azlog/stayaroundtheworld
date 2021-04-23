<?php
defined('admin') or exit;
// SQL query that will get all the accounts from the database ordered by the ID column
$stmt = $pdo->prepare('SELECT * FROM accounts ORDER BY id DESC');
$stmt->execute();
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?=template_admin_header('Accounts', 'accounts')?>

<h2>Accounts</h2>

<div class="links">
    <a href="index.php?page=account">Create Account</a>
</div>

<div class="content-block">
    <div class="table">
        <table>
            <thead>
                <tr>
                    <td class="responsive-hidden">#</td>
                    <td>Email</td>
                    <td>Name</td>
                    <td>Address</td>
                    <td>Admin</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($accounts)): ?>
                <tr>
                    <td colspan="8" style="text-align:center;">There are no accounts</td>
                </tr>
                <?php else: ?>
                <?php foreach ($accounts as $account): ?>
                <tr class="details" onclick="location.href='index.php?page=account&id=<?=$account['id']?>'">
                    <td class="responsive-hidden"><?=$account['id']?></td>
                    <td><?=$account['email']?></td>
                    <td><?=$account['first_name']?> <?=$account['last_name']?></td>
                    <td>
                        <?=$account['address_street']?><br>
                        <?=$account['address_city']?><br>
                        <?=$account['address_state']?><br>
                        <?=$account['address_zip']?><br>
                        <?=$account['address_country']?><br>
                    </td>
                    <td><?=$account['admin']==1?'true':'false'?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?=template_admin_footer()?>
