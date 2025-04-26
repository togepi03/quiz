<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['quiz_result'])) {
    header("Location: home.php");
    exit();
}

$result = $_SESSION['quiz_result'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - Quiz System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background-color: white !important;
            box-shadow: 0 2px 4px rgba(0,0,0,.08);
        }

        .result-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .result-card {
            background-color: white;
            border-radius: 1rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 2rem;
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        .score-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 1rem auto 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            color: white;
            position: relative;
        }

        .score-circle.excellent {
            background-color: var(--success-color);
        }

        .score-circle.good {
            background-color: #4e73df;
        }

        .score-circle.average {
            background-color: #f6c23e;
        }

        .score-circle.poor {
            background-color: #e74a3b;
        }

        .result-details {
            margin-bottom: 2rem;
        }

        .result-message {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap me-2"></i>
                Online Quiz System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="result-container">
        <div class="result-card">
            <h2 class="mb-4">Quiz Results</h2>
            
            <?php
            $percentage = $result['percentage'];
            $scoreClass = '';
            $message = '';
            
            if ($percentage >= 90) {
                $scoreClass = 'excellent';
                $message = 'Excellent! Outstanding performance!';
            } elseif ($percentage >= 70) {
                $scoreClass = 'good';
                $message = 'Good job! Well done!';
            } elseif ($percentage >= 50) {
                $scoreClass = 'average';
                $message = 'Not bad! Keep practicing!';
            } else {
                $scoreClass = 'poor';
                $message = 'You need more practice. Keep trying!';
            }
            ?>
            
            <div class="score-circle <?= $scoreClass ?>">
                <?= $percentage ?>%
            </div>
            
            <div class="result-details">
                <p class="result-message"><?= $message ?></p>
                <p class="mb-0">You scored</p>
                <h3><?= $result['score'] ?> out of <?= $result['total'] ?></h3>
            </div>
            
            <div class="d-grid gap-2">
                <a href="quiz.php" class="btn btn-primary">
                    <i class="fas fa-redo me-2"></i>Try Again
                </a>
                <a href="home.php" class="btn btn-secondary">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
unset($_SESSION['quiz_result']);
?>
