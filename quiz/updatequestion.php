<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: loginquiz.php");
    exit();
}

$message = '';
$question = null;
$sections = [];

$sectionsQuery = $conn->query("SELECT * FROM tbl_sections ORDER BY section_name");
while ($row = $sectionsQuery->fetch_assoc()) {
    $sections[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questionId = $_POST['question_id'];
    $sectionId = $_POST['section_id'];
    $question_text = $_POST['question'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = $_POST['option_c'];
    $option_d = $_POST['option_d'];
    $correct_answer = $_POST['correct_answer'];

    $stmt = $conn->prepare("UPDATE tbl_quiz SET 
        section_id = ?,
        quiz_question = ?,
        option_a = ?,
        option_b = ?,
        option_c = ?,
        option_d = ?,
        correct_answer = ?
        WHERE tbl_quiz_id = ?");

    $stmt->bind_param("issssssi", 
        $sectionId,
        $question_text,
        $option_a,
        $option_b,
        $option_c,
        $option_d,
        $correct_answer,
        $questionId
    );

    if ($stmt->execute()) {
        $message = "Question updated successfully";
        header("Location: home.php?message=" . urlencode($message));
        exit();
    } else {
        $message = "Error updating question: " . $conn->error;
    }
    $stmt->close();
} 
elseif (isset($_GET['question_id'])) {
    $questionId = $_GET['question_id'];
    $stmt = $conn->prepare("SELECT * FROM tbl_quiz WHERE tbl_quiz_id = ?");
    $stmt->bind_param("i", $questionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $question = $result->fetch_assoc();
    $stmt->close();
}

if (!$question && !isset($_GET['question_id'])) {
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Question</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-image: url('bbb.jpg');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            margin-top: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Update Question</h2>
        
        <?php if ($message): ?>
        <div class="alert alert-<?php echo strpos($message, 'Error') !== false ? 'danger' : 'success'; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="question_id" value="<?php echo $question['tbl_quiz_id']; ?>">
            
            <div class="mb-3">
                <label for="section" class="form-label">Section</label>
                <select class="form-select" name="section_id" required>
                    <?php foreach ($sections as $section): ?>
                        <option value="<?php echo $section['section_id']; ?>" 
                            <?php echo $section['section_id'] == $question['section_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($section['section_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="question" class="form-label">Question</label>
                <textarea class="form-control" name="question" rows="3" required><?php echo htmlspecialchars($question['quiz_question']); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Options</label>
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="option_a" placeholder="Option A" 
                            value="<?php echo htmlspecialchars($question['option_a']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="option_b" placeholder="Option B" 
                            value="<?php echo htmlspecialchars($question['option_b']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="option_c" placeholder="Option C" 
                            value="<?php echo htmlspecialchars($question['option_c']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="option_d" placeholder="Option D" 
                            value="<?php echo htmlspecialchars($question['option_d']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Correct Answer</label>
                <select class="form-select" name="correct_answer" required>
                    <option value="a" <?php echo $question['correct_answer'] == 'a' ? 'selected' : ''; ?>>Option A</option>
                    <option value="b" <?php echo $question['correct_answer'] == 'b' ? 'selected' : ''; ?>>Option B</option>
                    <option value="c" <?php echo $question['correct_answer'] == 'c' ? 'selected' : ''; ?>>Option C</option>
                    <option value="d" <?php echo $question['correct_answer'] == 'd' ? 'selected' : ''; ?>>Option D</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Question
                </button>
                <a href="home.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
