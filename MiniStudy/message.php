<?php
session_start(); // Start the session

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect user back to login page if not logged in
    header("Location: index.php");
    exit();
}

// Include database connection
include "connect.php"; // Assuming you have a file called connect.php

// Retrieve form data
$recipient = $_POST["recipient"];
$message = $_POST["message"];
$sender = $_SESSION['email']; // Assuming the user's email is stored in the session

// Prepare SQL statement to insert message into the database
$stmt = $conn->prepare("INSERT INTO messages (sender, recipient, message, sent_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("sss", $sender, $recipient, $message);

// Execute statement
$stmt->execute();

// Close statement
$stmt->close();

// Close database connection
$conn->close();

// Redirect back to the previous page (or any other desired page)
header("Location: main.php");
exit();
?>
