<?php
include 'db_connect.php';

if ($conn) {
    echo "Database connected successfully!";
} else {
    echo "Database connection failed!";
}
?>
