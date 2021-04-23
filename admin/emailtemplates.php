<?php
defined('admin') or exit;
if (isset($_POST['emailtemplate'])) {
    file_put_contents('../order-details-template.php', $_POST['emailtemplate']);
}
// Read the order details template PHP file
$contents = file_get_contents('../order-details-template.php');
?>
<?=template_admin_header('Email Templates', 'emailtemplates')?>

<h2>Email Templates</h2>

<div class="content-block">
    <form action="" method="post" class="form responsive-width-100">
        <label for="emailtemplate">Order Details Template:</label>
        <textarea name="emailtemplate" id="emailtemplate"><?=$contents?></textarea>
        <input type="submit" value="Save">
    </form>
</div>

<?=template_admin_footer()?>
