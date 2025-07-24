<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pod_id = $_POST['pod_id'];
    $type = $_POST['type'];
    $value = $_POST['value'];

    if ($type === 'light' || $type === 'pump') {
        $sql = "UPDATE pot_trees SET $type = ? WHERE POD_ID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "is", $value, $pod_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
}
?>
