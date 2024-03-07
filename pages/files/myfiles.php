<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); // Start the session

// Check if the user is not logged in, redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Include the database connection
include_once "../../connection.php";

// Function to fetch and display files for a given table
function displayFiles($conn, $table)
{
    // Get the user ID from the session
    $user_id = $_SESSION['user_id'];

    // Query to fetch files from the specified table for the current user
    $query = "SELECT fileid, file_name, file_path FROM $table WHERE userid = $1";
    $result = pg_query_params($conn, $query, array($user_id));

    if ($result && pg_num_rows($result) > 0) {
        // Display files in a table
        echo "<table border='1'>";
        echo "<tr><th>File Name</th><th>Action</th></tr>";

        while ($row = pg_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td style='text-align:center;'>{$row['file_name']}</td>";
            echo "<td style='text-align:center;'>";

            if ($table === 'files') {
                echo "<form method='post' action='encrypt.php' style='display:inline-block;'>
                        <input type='hidden' name='file_id' value='{$row['fileid']}' />
                        <button type='submit' name='encrypt_file'>Encrypt</button>
                      </form>";
            } else {
                echo "<form method='get' action='decrypt.php' style='display:inline-block;'>
                        <input type='hidden' name='file_id' value='{$row['fileid']}' />
                        <button type='submit' name='decrypt_file'>Decrypt</button>
                      </form>";

                // Check if the file has been decrypted
                $file_path = $row['file_path'];
                $file_exists = file_exists($file_path);

                // Display download button if file has been decrypted, otherwise, disable it
                if ($file_exists) {
                    echo "<a href='download.php?file_id={$row['fileid']}&table=$table'><button>Download</button></a>";
                } else {
                    echo "<button disabled>Download</button>";
                }
            }

            echo "<form method='post' action='delete.php' style='display:inline-block;'>
                    <input type='hidden' name='file_id' value='{$row['fileid']}' />
                    <button type='submit' name='delete_file'>Delete</button>
                  </form>";

            echo "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "No files found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Files</title>
    <style>
        table {
            border-collapse: collapse;
            width: 50%;
        }

        th,
        td {
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        button {
            padding: 5px 10px;
            margin: 5px;
        }
    </style>
</head>

<body>
    <h1>
        <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ''; ?>
    </h1>
    <a href="../../fileuploadtest/index.php">
        <button>Home</button>
    </a>
    <hr>
    <h2>Non-Encrypted Files</h2>
    <?php displayFiles($conn, "files"); ?>
    <hr>
    <h2>Encrypted Files</h2>
    <?php displayFiles($conn, "encrypted_files"); ?><br><br>
    <hr>
    <h2>Decrypted Files</h2>
    <?php displayFiles($conn, "decrypted_files"); ?><br><br>
    <hr>
    <h2>Externally Encrypted Files</h2>
    <?php displayFiles($conn, "externally_encrypted_files"); ?><br><br>
    <hr>
    <h2>Upload Files</h2>
    <form action="../../fileuploadtest/upload.php" method="post" enctype="multipart/form-data">
        Select file to upload:
        <input type="file" name="fileToUpload" id="fileToUpload" />
        <input type="submit" value="Upload File" name="submit" />
    </form>
    <br>
</body>

</html>