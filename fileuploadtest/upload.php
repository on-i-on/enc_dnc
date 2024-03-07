<?php
include_once 'includes/function.php';
uploadFile();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <!-- HTML content here -->
</body>

</html>

<?php
header("Location: ../pages/files/myfiles.php");
exit; // Ensure no further code is executed after redirection
?>