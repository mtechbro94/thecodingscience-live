<?php
// test_upload.php - Place in root folder, then delete after testing

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>FILES Data:</h2>";
    echo "<pre>" . print_r($_FILES, true) . "</pre>";
    
    if (isset($_FILES['test_image']) && $_FILES['test_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/assets/images/';
        $filename = 'test_' . time() . '_' . $_FILES['test_image']['name'];
        
        if (move_uploaded_file($_FILES['test_image']['tmp_name'], $upload_dir . $filename)) {
            echo "<p style='color:green'>SUCCESS! Uploaded as: $filename</p>";
        } else {
            echo "<p style='color:red'>FAILED to move file. Check permissions.</p>";
        }
    }
}
?>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="test_image" accept="image/*">
    <button type="submit">Test Upload</button>
</form>
