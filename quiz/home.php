<?php
session_start();
//admin dashboard
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: loginquiz.php");
    exit();
}

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: user.php");
    exit();
}

include("conn.php");

$username = $_SESSION['username'];
$email = $_SESSION['email'];

$sections_query = "SELECT s.*, COUNT(q.tbl_quiz_id) as question_count 
                  FROM tbl_sections s 
                  LEFT JOIN tbl_quiz q ON s.section_id = q.section_id 
                  GROUP BY s.section_id 
                  ORDER BY s.section_name";
$sections_result = $conn->query($sections_query);

$questions_query = "SELECT q.*, s.section_name 
                   FROM tbl_quiz q 
                   JOIN tbl_sections s ON q.section_id = s.section_id 
                   ORDER BY s.section_name, q.quiz_question";
$questions_result = $conn->query($questions_query);

$message = isset($_GET['message']) ? $_GET['message'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Quiz System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-image: url('bbb.jpg');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
        }
        .content-wrapper {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px;
            margin: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .section-card {
            transition: all 0.3s ease;
        }
        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .navbar {
            background-color: rgba(255, 255, 255, 0.95);
        }
        .table {
            background-color: white;
        }
        .action-buttons .btn {
            margin: 0 2px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap me-2"></i>Quiz Admin
            </a>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($username); ?>
                </span>
                <a href="logout.php" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="content-wrapper">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3><i class="fas fa-folder me-2"></i>Sections</h3>
                        <a href="addquestion.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add New Section
                        </a>
                    </div>
                    
                    <div class="row">
                        <?php while ($section = $sections_result->fetch_assoc()): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card section-card">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($section['section_name']); ?></h5>
                                    <p class="card-text">
                                        <?php echo htmlspecialchars($section['section_description']); ?><br>
                                        <small class="text-muted">
                                            <i class="fas fa-question-circle me-1"></i>
                                            <?php echo $section['question_count']; ?> questions
                                        </small>
                                    </p>
                                    <div class="action-buttons">
                                        <a href="deletequestion.php?section_id=<?php echo $section['section_id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this section and all its questions?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="content-wrapper">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3><i class="fas fa-question-circle me-2"></i>Questions</h3>
                        <a href="addquestion.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add New Question
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Section</th>
                                    <th>Question</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($question = $questions_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($question['section_name']); ?></td>
                                    <td><?php echo htmlspecialchars($question['quiz_question']); ?></td>
                                    <td class="action-buttons">
                                        <a href="updatequestion.php?question_id=<?php echo $question['tbl_quiz_id']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="deletequestion.php?question_id=<?php echo $question['tbl_quiz_id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this question?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>