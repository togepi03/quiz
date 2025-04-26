<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: loginquiz.php");
    exit();
}

include("conn.php");

$alter_table = $conn->query("ALTER TABLE tbl_result 
    ADD COLUMN IF NOT EXISTS correct_answers TEXT AFTER total_score,
    ADD COLUMN IF NOT EXISTS wrong_answers TEXT AFTER correct_answers");

if (!$alter_table) {
    die("Error adding columns: " . $conn->error);
}

$check_table = $conn->query("SHOW TABLES LIKE 'tbl_result'");
if ($check_table->num_rows == 0) {
    die("Table tbl_result does not exist!");
}

$table_structure = $conn->query("DESCRIBE tbl_result");
echo "Table structure:<br>";
while ($row = $table_structure->fetch_assoc()) {
    echo "Column: " . $row['Field'] . " Type: " . $row['Type'] . "<br>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    $totalQuestions = 0;
    $correctAnswers = [];
    $wrongAnswers = [];
    
    // Get all answers submitted by the user
    $userAnswers = isset($_POST['answer']) ? $_POST['answer'] : array();
    
    $stmt = $conn->prepare("SELECT tbl_quiz_id, quiz_question, correct_answer, option_a, option_b, option_c, option_d FROM tbl_quiz WHERE section_id = ?");
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $section_id = $_POST['section_id'];
    $stmt->bind_param("i", $section_id);
    
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $questionId = $row['tbl_quiz_id'];
        $totalQuestions++;
        
        // Check if user provided an answer for this question
        if (isset($userAnswers[$questionId])) {
            $userAnswer = $userAnswers[$questionId];
            
            // Get the correct answer value based on the correct_answer letter
            $correctAnswerValue = $row['option_' . strtolower($row['correct_answer'])];
            
            // Compare user's answer with correct answer value
            if ($userAnswer === $correctAnswerValue) {
                $score++;
                $correctAnswers[] = $row['quiz_question'];
            } else {
                $wrongAnswers[] = $row['quiz_question'];
            }
        } else {
            $wrongAnswers[] = $row['quiz_question'];
        }
    }
    
    $stmt->close();
    
    $percentage = ($totalQuestions > 0) ? round(($score / $totalQuestions) * 100) : 0;
    
    $correctJson = json_encode($correctAnswers);
    $wrongJson = json_encode($wrongAnswers);
    
    $username = $_SESSION['username'];
    
    $insertQuery = "INSERT INTO tbl_result (quiz_taker, total_score, correct_answers, wrong_answers, date_taken) 
                   VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($insertQuery);
    if (!$stmt) {
        die("Error preparing insert statement: " . $conn->error . "\nQuery: " . $insertQuery);
    }
    

    
    if (!$stmt->bind_param("ssss", 
        $_SESSION['username'], 
        $percentage,
        $correctJson,
        $wrongJson
    )) {
        die("Error binding parameters: " . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        die("Error executing insert: " . $stmt->error);
    }
    
    $resultId = $stmt->insert_id;
    $stmt->close();
    
    $_SESSION['quiz_result'] = [
        'score' => $score,
        'total' => $totalQuestions,
        'percentage' => $percentage,
        'correct_answers' => $correctAnswers,
        'wrong_answers' => $wrongAnswers,
        'result_id' => $resultId
    ];
    
    header("Location: quiz_result.php");
    exit();
} else {
    header("Location: quiz.php");
    exit();
}
?>
