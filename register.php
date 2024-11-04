<?php
require_once "config.php";

$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } elseif(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))){
        $username_err = "Username can only contain letters, numbers, and underscores.";
    } else{
        $sql = "SELECT id FROM users WHERE username = :username";
        
        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            
            $param_username = trim($_POST["username"]);
            
            if($stmt->execute()){
                if($stmt->rowCount() == 1){
                    $username_err = "This username is already taken.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            unset($stmt);
        }
    }
    
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have at least 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err)){
        
        $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
         
        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            
            if($stmt->execute()){
                header("location: index.php");
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            unset($stmt);
        }
    }
    
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font: 14px sans-serif;
            background-color: #000; /* Black background */
            color: #fff; /* White text */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full viewport height for centering */
            margin: 0;
        }
        .wrapper {
            width: 100%;
            max-width: 400px; /* Set a max-width for the form */
            padding: 30px; /* Increased padding for better spacing */
            background-color: #1a1a1a; /* Dark gray background for the form */
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5); /* Soft shadow for depth */
        }
        h2 {
            color: #ff4081; /* Pink color for the heading */
            text-align: center; /* Center the heading */
            margin-bottom: 20px; /* Space below the heading */
        }
        .form-control {
            background-color: #121212; /* Darker input field */
            color: #fff; /* White text */
            border: 1px solid #ff4081; /* Pink border */
        }
        .form-control:focus {
            background-color: #121212; /* Dark background on focus */
            border-color: #ff80ab; /* Lighter pink border on focus */
            box-shadow: 0 0 5px rgba(255, 20, 147, 0.5); /* Pink glow effect on focus */
        }
        .btn-primary {
            background-color: #ff4081; /* Pink button */
            border: none;
            transition: background-color 0.3s; /* Smooth transition */
            width: 100%; /* Full width button */
        }
        .btn-primary:hover {
            background-color: #ff80ab; /* Lighter pink on hover */
        }
        .btn-secondary {
            background-color: #333; /* Darker gray for reset button */
            color: #fff; /* White text */
            border: none;
            width: 100%; /* Full width button */
        }
        .btn-secondary:hover {
            background-color: #444; /* Slightly lighter gray on hover */
        }
        .alert-danger {
            background-color: #ff5252; /* Bright red for error messages */
            color: #fff; /* White text */
            border-radius: 5px; /* Slight rounding of corners */
        }
        p {
            text-align: center; /* Center the paragraph */
        }
        a {
            color: #ffccbc; /* Light pink for links */
            text-decoration: underline; /* Underline links */
        }
        a:hover {
            color: #ff80ab; /* Lighter pink on hover */
        }
    </style> 
</head>
<body>
    <div class="wrapper">
        <h2>Sign Up</h2>
        <p>Please fill this form to create an account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-secondary ml-2" value="Reset">
            </div>
            <p>Already have an account? <a href="index.php">Login here</a>.</p>
        </form>
    </div>    
</body>
</html>
