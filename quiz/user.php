<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: loginquiz.php");
    exit();
}

if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    header("Location: home.php");
    exit();
}

$username = $_SESSION['username'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Quiz System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-image: url('bbb.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            position: relative;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 1;
        }

        .content {
            position: relative;
            z-index: 2;
            padding: 2rem;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px);
        }

        .welcome-text {
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .card-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #4e73df;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 3;
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    
    <a href="logout.php" class="btn btn-danger logout-btn">
        <i class="fas fa-sign-out-alt me-2"></i>Logout
    </a>

    <div class="container content">
        <div class="text-center mb-5">
            <h1 class="welcome-text">Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
            <p class="text-light">What would you like to do today?</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-4 mb-4">
                <a href="quiz.php" class="text-decoration-none">
                    <div class="card h-100 text-center p-4">
                        <div class="card-body">
                            <div class="card-icon">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <h4 class="card-title">Take Quiz</h4>
                            <p class="card-text">Start a new quiz and test your knowledge</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4 mb-4">
                <a href="results.php" class="text-decoration-none">
                    <div class="card h-100 text-center p-4">
                        <div class="card-body">
                            <div class="card-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <h4 class="card-title">View Results</h4>
                            <p class="card-text">Check your quiz history and performance</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
