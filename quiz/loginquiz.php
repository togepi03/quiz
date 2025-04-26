<?php
include 'conn.php';
session_start();

// Clear any existing session data
session_unset();
session_destroy();
session_start();

$message = "";
$toastClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepare SQL statement
    $sql = "SELECT * FROM userdata WHERE email = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['loggedin'] = true;
                $_SESSION['email'] = $email;
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                // Redirect based on is_admin value
                if ($user['is_admin'] == 1) {
                    header("Location: home.php");
                } else {
                    header("Location: user.php");
                }
                exit();
            } else {
                $message = "Invalid password";
                $toastClass = "bg-danger";
            }
        } else {
            $message = "No account found with that email";
            $toastClass = "bg-warning";
        }
        $stmt->close();
    } else {
        $message = "Something went wrong";
        $toastClass = "bg-danger";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="abc.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="wrapper">
        <form action="" method="POST">
            <h1>Login</h1>

            <?php if ($message): ?>
                <div class="toast <?php echo $toastClass; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="input-box">
                <input type="email" placeholder="Email" name="email" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" placeholder="Password" name="password" required>
                <i class='bx bxs-lock'></i>
            </div>
            <div class="remember-forgot">
                <label><input type="checkbox">Remember me</label>
            </div>
            <button type="submit" class="btn">Login</button>
            <div class="register-link">
                <p>Don't have an account? <a href="register.php">Register</a></p>
            </div>
        </form>
    </div>
</body>
</html>