<?php
include "connect.php";
session_start();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Retrieve form data
    $email = $_POST["email"];
    $password = $_POST["password"];
    
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ? AND password = ?");
    
    // Check if prepare() succeeded
    if ($stmt === false) {
        // Handle the error here, maybe log it
        die("Prepare failed: " . htmlspecialchars($conn->error));
    }
    
    // Bind parameters
    $stmt->bind_param("ss", $email, $password);
    
    // Execute statement
    $stmt->execute();
    
    // Get result
    $result = $stmt->get_result();
    
    // Check if any row is returned
    if ($result->num_rows > 0) {
        // User exists and password matches
        // Fetch the user data to get the name
        $user = $result->fetch_assoc();
        // Retrieve the name from the fetched user data
        $name = $user['name'];
        // Set user's name in session
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        
        // Redirect to main.php after successful login
        header("Location: main.php");
        exit(); // Ensure script stops executing after redirection
    } else {
        // User not found or password incorrect
        echo "Login failed. Please check your credentials.";
    }
    
    // Close statement
    $stmt->close();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body{
            background-color: slategray;
        }
        .container .card{
            margin-top: 17%;
            left: 40%;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card" style="width: 18rem;">
            <div class="card-body">
                <div class="mb-3">
                    <form action="" method="post">
                        <label for="exampleFormControlInput1" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="exampleFormControlInput1" placeholder="name@example.com" name="email">
                </div>
                <label for="inputPassword5" class="form-label">Password</label>
                <input type="password" id="inputPassword5" class="form-control" aria-describedby="passwordHelpBlock" placeholder="*******" name="password"><br>
                <input class="btn btn-primary" type="submit" value="Submit" name="btn">
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
