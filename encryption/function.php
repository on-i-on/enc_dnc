<?php
include_once '../connection.php';

function getEncryptedContent($fileId, $conn)
{
    // Fetch encrypted content from the database based on the provided file ID
    $query = "SELECT modified_content FROM file WHERE fileid = $1";
    $result = pg_query_params($conn, $query, array($fileId));

    // Check if the file record exists
    if ($result && pg_num_rows($result) > 0) {
        // Fetch encrypted content
        $row = pg_fetch_assoc($result);
        $encryptedContent = $row['modified_content'];

        // Base64 decode the content to get the original content
        $originalContent = base64_decode($encryptedContent);

        return $originalContent;
    } else {
        // Return false if file record not found
        return false;
    }
}

