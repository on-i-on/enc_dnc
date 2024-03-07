<?php
session_start(); // Start the session

// Check if the user is not logged in, redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

// Include database connection
include_once "../../connection.php";

// Check if the form for deleting a file is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_file"])) {
    // Get the selected file ID from the form
    $selected_file_id = $_POST["file_id"];

    // Get the user ID from the session
    $user_id = $_SESSION['user_id'];

    // Check if the file exists in the 'files' table
    $query = "SELECT * FROM files WHERE fileid = $1 AND userid = $2";
    $result = pg_query_params($conn, $query, array($selected_file_id, $user_id));

    if (pg_num_rows($result) > 0) {
        // File exists in 'files' table, delete it from there
        $delete_query = "DELETE FROM files WHERE fileid = $1 AND userid = $2";
        $delete_result = pg_query_params($conn, $delete_query, array($selected_file_id, $user_id));
    } else {
        // File exists in 'encrypted_files' table, delete it from there
        $delete_query = "DELETE FROM encrypted_files WHERE fileid = $1 AND userid = $2";
        $delete_result = pg_query_params($conn, $delete_query, array($selected_file_id, $user_id));

        // Also delete from 'decrypted_files' table
        $delete_decrypted_query = "DELETE FROM decrypted_files WHERE fileid = $1 AND userid = $2";
        $delete_decrypted_result = pg_query_params($conn, $delete_decrypted_query, array($selected_file_id, $user_id));
    }

    if ($delete_result && $delete_decrypted_result) {
        // File deleted successfully
        echo "<script>alert('File deleted successfully.'); window.location.href = 'myfiles.php';</script>";
        exit;
    } else {
        // Error occurred while deleting file
        echo "<script>alert('Error: Failed to delete file.'); window.location.href = 'myfiles.php';</script>";
        exit;
    }
} else {
    // If the form is not submitted, redirect to myfiles.php
    header("Location: myfiles.php");
    exit;
}
