<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection and necessary functions
include_once '../connection.php';
include 'function.php';

// Retrieve files uploaded by the current user
session_start();
$userId = $_SESSION['user_id'];
$query = "SELECT fileid, file_name FROM file WHERE userid = $1";
$result = pg_query_params($conn, $query, array($userId));
$fileOptions = pg_fetch_all($result);

// Function to decrypt file content using provided password and update the database
function decryptAndUpdateFileContent($fileId, $password, $conn)
{
    // Retrieve encrypted content from the database based on the provided file ID
    $query = "SELECT modified_content FROM file WHERE fileid = $1";
    $result = pg_query_params($conn, $query, array($fileId));

    // Check if the file record exists
    if ($result && pg_num_rows($result) > 0) {
        // Fetch encrypted content
        $row = pg_fetch_assoc($result);
        $encryptedContent = $row['modified_content'];

        // Calculate the sum of individual digits in the password
        $passwordSum = array_sum(str_split($password));

        // Decrypt the content using the provided password
        $decryptedContent = '';
        for ($i = 0; $i < strlen($encryptedContent); $i += 4) {
            // Extract each four-digit chunk of the encrypted content
            $chunk = substr($encryptedContent, $i, 4);
            // Convert the chunk to integer and subtract the password sum
            $decryptedChunk = intval($chunk) - $passwordSum;
            // Append the decrypted chunk to the decrypted content
            $decryptedContent .= $decryptedChunk;
        }

        // Update the database with the decrypted content
        $updateQuery = "UPDATE file SET modified_content = $1 WHERE fileid = $2";
        pg_query_params($conn, $updateQuery, array($decryptedContent, $fileId));

        // Return the decrypted content
        return $decryptedContent;
    } else {
        // Return false if file record not found
        return false;
    }
}

// Check if decrypt button is clicked
if (isset($_POST['decrypt'])) {
    // Get the selected file ID and password
    $selectedFileId = $_POST['file_id'];
    $password = $_POST['password'];

    // Decrypt file content using provided password and update the database
    $decryptedContent = decryptAndUpdateFileContent($selectedFileId, $password, $conn);

    // Check if decryption was successful
    if ($decryptedContent !== false) {
        // Display success message
        echo "<script>alert('File decrypted successfully.');</script>";

        // Redirect to download file
        header("Location: ../fileuploadtest/download.php?file_id=$selectedFileId");
        exit;
    } else {
        // Display error message if decryption failed
        echo "<script>alert('Error: File decryption failed.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decrypt Files</title>
</head>

<body>
    <h1>Decrypt Files</h1>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <label for="file">Select a file to decrypt:</label>
        <select name="file_id" id="file">
            <?php foreach ($fileOptions as $option): ?>
                <option value="<?php echo $option['fileid']; ?>">
                    <?php echo $option['file_name']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <label for="password">Enter Password:</label>
        <input type="password" name="password" id="password">
        <br><br>
        <input type="submit" name="decrypt" value="Decrypt">
    </form>
</body>

</html>