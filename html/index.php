<?php
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Uploader</title>
</head>
<body>
<form action="index.php" method="post" enctype="multipart/form-data">
    <input type="file" name="uploadfile" id="uploadfile">
    <input type="submit" value="Upload" name="submit">
</form>

<?php
include 'dbHandler.php';
$dbhandler = new DBHandler();
if (file_exists($_FILES['uploadfile']['tmp_name'])) {

    $allowed_extensions = array('text/xml', 'text/csv');

    if (in_array($_FILES['uploadfile']['type'], $allowed_extensions)) {
        $dbhandler->uploadHandler($_FILES['uploadfile']['tmp_name'], $_FILES['uploadfile']['type'], $_FILES['uploadfile']['name']);
    } else {
        die("<span class='Error'>Ungültige Dateiendung. Nur xml und csv-Dateien sind erlaubt</span>");
    }

}
?>

</body>
</html>