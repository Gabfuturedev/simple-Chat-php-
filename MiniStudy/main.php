<?php
session_start(); // Start the session

// Check if user is logged in
if (isset($_SESSION['email'])) {
    // Include database connection
    include "connect.php"; // Assuming you have a file called connect.php
    
    // Retrieve email from session
    $email = $_SESSION['email'];
    
    // Prepare SQL statement to retrieve user's name
    $stmt = $conn->prepare("SELECT name FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($name);
    
    // Fetch user's name
    if ($stmt->fetch()) {
        // User's name found, store it in a variable $name
        // echo "Welcome, $name!";
    } else {
        // User's name not found
        echo "Welcome!";
    }
    
    // Close statement
    $stmt->close();
} else {
    // Redirect user back to login page if not logged in
    header("Location: index.php");
    exit();
}

// Handle sending messages
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['recipient']) && isset($_POST['message'])) {
        $recipient = $_POST['recipient'];
        $message = $_POST['message'];
        $timestamp = date("Y-m-d H:i:s");
        
        // Prepare SQL statement to insert message into database
        $stmt_insert = $conn->prepare("INSERT INTO messages (sender, recipient, message, sent_at) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("ssss", $name, $recipient, $message, $timestamp);
        $stmt_insert->execute();
        $stmt_insert->close();
        
        // Redirect back to the same page after sending the message
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch received messages from the database
$received_messages_query = "SELECT sender, message, sent_at FROM messages WHERE recipient = ?";
$stmt_received = $conn->prepare($received_messages_query);
$stmt_received->bind_param("s", $email); // Use $email instead of $name
$stmt_received->execute();
$result_received = $stmt_received->get_result();

// Fetch sent messages from the database
$sent_messages_query = "SELECT recipient, message, sent_at FROM messages WHERE sender = ?";
$stmt_sent = $conn->prepare($sent_messages_query);
$stmt_sent->bind_param("s", $name);
$stmt_sent->execute();
$result_sent = $stmt_sent->get_result();

// Close database connection
$conn->close();
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
    .message-container {
        margin-bottom: 15px;
        display: flex;
        flex-direction: column;
        
    }

    .message {
        padding: 10px;
        border-radius: 10px;
        word-wrap: break-word;
        
    }

    .incoming .message {
        background-color: #fff;
        border: 1px solid #ccc;
        margin-left: auto; /* Push received messages to the left */
        border-top-left-radius: 0;
    }

    .outgoing .message {
        background-color: #007bff;
        color: #fff;
        margin-right: auto; /* Push sent messages to the right */
        border-top-right-radius: 0;
    }

    .timestamp {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
        text-align: right;
    }

    .message-sender {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .message-form {
        margin-top: 20px;
    }

    .message-form textarea {
        resize: none;
    }
</style>



</head>
<body>
<nav class="navbar bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand"><?php echo ucwords($name)?></a>
        <form class="d-flex" role="search">
            <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-success" type="submit">Search</button>
        </form>
    </div>
    <form method="post">
        <button type="submit" name="logout">Logout</button>
    </form>
    <?php
    if(isset($_POST['logout'])) {
        // Destroy the session
        session_destroy();
    
        // Redirect to the index page
        header("Location: index.php");
        exit;
    }
    ?>
</nav>
<div class="container">
    



<!-- inbox -->
   
    
    
   <!-- Display Messages -->

    <h2>Messages</h2>
    <div class="card" style="height: 500px; overflow-y: auto" >
        <div class="card-body" style="width:100%;" >
            <?php
            // Display received and sent messages
            while ($row_received = $result_received->fetch_assoc()) {
                $sender = htmlspecialchars($row_received['sender']);
                $message = htmlspecialchars($row_received['message']);
                $sent_at = htmlspecialchars($row_received['sent_at']);
            ?>
                <div class="message-container incoming">
                    <div class="message">
                        <div class="message-content">
                            <p><?php echo $sender; ?></p>
                            <p><?php echo $message; ?></p>
                            <span class="timestamp"><?php echo $sent_at; ?></span>
                        </div>
                    </div>
                </div>
            <?php
            }

            // Reset the result set to display sent messages
            $result_sent->data_seek(0);

            // Display sent messages
            while ($row_sent = $result_sent->fetch_assoc()) {
                $recipient = htmlspecialchars($row_sent['recipient']);
                $message = htmlspecialchars($row_sent['message']);
                $sent_at = htmlspecialchars($row_sent['sent_at']);
            ?>
                <div class="message-container outgoing">
                    <div class="message">
                        <div class="message-content">
                            <p><?php echo $message; ?></p>
                            <span class="timestamp"><?php echo $sent_at; ?></span>
                        </div>
                        
                    </div>
                </div> 
            <?php
            }
            ?> 
            
        </div>
    </div>
    

</div>
    <form action="" method="post">
        <div class="mb-3">
            <!-- <label for="recipient" class="form-label">Recipient:</label> -->
            <input type="text" class="form-control" id="recipient" name="recipient" placeholder="Recipient's Email" " >
        </div>
        <div class="mb-3">
            <label for="message" class="form-label">Message:</label>
            <textarea class="form-control" id="message" name="message" rows="3" placeholder="Type your message here"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send Message</button>
    </form>
    <?php 
    if(isset($_POST["user1"])){
        
    }
    ?>
     <script>
        // Function to load conversation when a user is clicked
        function loadConversation(user) {
            $.ajax({
                url: 'getConversation.php', // PHP script to fetch conversation
                type: 'POST',
                data: { user: user },
                success: function(response) {
                    $('#conversation').html(response); // Update conversation area
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }

        // Function to load inbox
        function loadInbox() {
            $.ajax({
                url: 'getInbox.php', // PHP script to fetch inbox
                type: 'GET',
                success: function(response) {
                    $('#inbox').html(response); // Update inbox area
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }

        // Load inbox when the page loads
        $(document).ready(function() {
            loadInbox();
        });
    </script>
<SCRipt>
    function setRecipient(email) {
    document.getElementById('recipient').value = email;
}

</SCRipt>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
