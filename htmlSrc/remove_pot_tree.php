<?php
// Include the database connection file
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['POT_ID'])) {
    $POT_ID = $_POST['POT_ID'];
    
    // Prepare the SQL delete statement
    $query = "DELETE FROM pot_trees WHERE POT_ID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $POT_ID);
    
    // Execute the statement
    if (mysqli_stmt_execute($stmt)) {
        // Close the statement and connection
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        // Redirect back to the main page after successful deletion
        header("Location: view_pot_tree.php");
        exit;
    } else {
        echo "Error deleting pot tree: " . mysqli_error($conn);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
} else {
    echo "Invalid request.";
}
?>
