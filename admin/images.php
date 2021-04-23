<?php
defined('admin') or exit;
if (isset($_FILES['upload_images'])) {
    $upload_images = $_FILES['upload_images'];
    $fileCount = count($upload_images['name']);
    for ($i = 0; $i < $fileCount; $i++) {
        if (file_exists('../imgs/' . $upload_images['name'][$i])) {
            $error .= 'Image already exists with this name: <b>' . $upload_images['name'][$i] . '</b><br>';
        } else {
            move_uploaded_file($upload_images['tmp_name'][$i], '../imgs/' . $upload_images['name'][$i]);
        }
    }
}
if (isset($_GET['delete']) && file_exists('../imgs/' . $_GET['delete'])) {
    unlink('../imgs/' . $_GET['delete']);
    $stmt = $pdo->prepare('DELETE FROM products_images WHERE img = ?');
    $stmt->execute([ $_GET['delete'] ]);
}
$imgs = glob('../imgs/*.{jpg,png,gif,jpeg,webp}', GLOB_BRACE);
?>
<?=template_admin_header('Images', 'images')?>

<h2>Images</h2>

<div class="content-block">
    <p class="error"><?=$error?></p>
    <form action="" method="post" class="form responsive-width-100" enctype="multipart/form-data">
        <div class="responsive-flex-column" style="display:flex;">
            <input type="file" id="images" name="upload_images[]" multiple required>
            <input type="submit" value="Upload Images" class="responsive-width-100">
        </div>
    </form>
    <div class="images">
        <?php foreach ($imgs as $img): ?>
        <div>
            <a href="index.php?page=images&delete=<?=basename($img)?>">
                <i class="fas fa-times"></i>
            </a>
            <img src="<?=$img?>" width="150" height="150" alt="">
            <span><?=basename($img)?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?=template_admin_footer()?>
