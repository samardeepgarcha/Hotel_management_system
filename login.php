<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, password_hash, role FROM users WHERE name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password, $role);
    
    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
        $_SESSION["user_id"] = $id;
        $_SESSION["role"] = $role;
        echo "Login successful! Redirecting...";
        header("refresh:2; url=dashboard.php");
    } else {
        echo "Invalid username or password.";
    }
    $stmt->close();
}
?>

<form method="post">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Login</button>
</form>
