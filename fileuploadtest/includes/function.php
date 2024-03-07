<?php
// Include the database connection
include 'connection.php';

function uploadFile()
{
    // Set the target directory for uploaded files (modify as needed)
    $target_dir = "uploads/";

    // Check if form submission occurred (prevents unnecessary file checks)
    if (isset($_POST["submit"])) {

        // Get the file name and ensure it is not empty
        $fileName = $_FILES["fileToUpload"]["name"];
        if (empty($fileName)) {
            echo "<script>alert('Error: Please select a file to upload.');</script>";
            exit; // Terminate script execution
        }

        // Extract the file extension (lowercase for case-insensitivity)
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Allow only specific text file extensions (modify as needed)
        $allowedExtensions = array("txt", "csv", "md", "html", "php", "js", "css", "pdf");
        if (!in_array($fileType, $allowedExtensions)) {
            echo "<script>alert('Error: Only " . implode(", ", $allowedExtensions) . " files are allowed.');</script>";
            exit; // Terminate script execution
        }

        // Get the user ID from the session
        session_start();
        $userid = $_SESSION['user_id'];

        // Check if a file with the same name and user ID exists in the database
        global $conn; // Access the database connection globally
        $query = "SELECT * FROM files WHERE file_name = $1 AND userid = $2";
        $params = array($fileName, $userid);
        $result = pg_query_params($conn, $query, $params);

        if (pg_num_rows($result) > 0) {
            echo "<script>alert('Error: A file with the same name and user already exists in the database.');</script>";
            exit; // Terminate script execution
        }

        // Attempt to move the uploaded file to the target location
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_dir . $fileName)) {
            echo "<script>alert('The file " . htmlspecialchars($fileName) . " has been uploaded successfully.');</script>";

            // Get the file path
            $filePath = $target_dir . $fileName;

            // Get the current date and time
            $uploadDate = date('Y-m-d H:i:s');

            // Store the file details in the database
            $query = "INSERT INTO files (userid, file_name, file_path, upload_date) VALUES ($1, $2, $3, $4)";
            $params = array($userid, $fileName, $filePath, $uploadDate);
            pg_query_params($conn, $query, $params);

            // Redirect to another page if needed

        } else {
            echo "<script>alert('Error: An error occurred while uploading the file.');</script>";
        }
    } else {
        // Display basic instructions if no form submission occurred
        echo "Select a text file (.txt, .csv, .md, etc.) to upload:";
    }
}

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
            } elseif ($table == 'decrypted_files') {
                echo "<form method='get' action='delete.php' style='display:inline-block;'>";
                echo "<input type='hidden' name='file_id' value='{$row['fileid']}' />";
                echo "<button type='submit' name='delete_file'>Delete</button>";
                echo "</form>";
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
        <input type='hidden' name='table_name' value='{$table}' />
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

