<?php
$conn = new mysqli("localhost", "root", "", "lms_university");

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
echo "✅ Connection successful!";
?>