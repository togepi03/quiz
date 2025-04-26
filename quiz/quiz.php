<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: loginquiz.php");
    exit();
}

// Include database connection
include 'conn.php';

$selected_section = isset($_GET['section']) ? $_GET['section'] : null;

// Get all sections
$sections_query = 'SELECT * FROM tbl_sections ORDER BY section_name';
$sections_result = $conn->query($sections_query);

// Get questions if section is selected
if ($selected_section) {
    $query = 'SELECT * FROM tbl_quiz WHERE section_id = ?';
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $selected_section);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Shuffle questions
    shuffle($questions);

    // Prepare and shuffle answers for each question
    foreach ($questions as &$question) {
        // Create array of answer options with their values
        $answers = array(
            array('value' => $question['option_a'], 'is_correct' => $question['correct_answer'] === 'a'),
            array('value' => $question['option_b'], 'is_correct' => $question['correct_answer'] === 'b'),
            array('value' => $question['option_c'], 'is_correct' => $question['correct_answer'] === 'c'),
            array('value' => $question['option_d'], 'is_correct' => $question['correct_answer'] === 'd')
        );
        
        // Shuffle the answers
        shuffle($answers);
        
        // Update the options
        $question['option_a'] = $answers[0]['value'];
        $question['option_b'] = $answers[1]['value'];
        $question['option_c'] = $answers[2]['value'];
        $question['option_d'] = $answers[3]['value'];
        
        // Store the correct answer value
        foreach ($answers as $index => $answer) {
            if ($answer['is_correct']) {
                $question['correct_answer_value'] = $answer['value'];
                break;
            }
        }
    }
    unset($question); // Break the reference
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz - Quiz System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-image: url('bbb.jpg');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .timer-container {
            position: fixed;
            top: 70px;
            right: 20px;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .timer-container i {
            color: #007bff;
        }
        #timer {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .section-card {
            transition: transform 0.2s;
            cursor: pointer;
            background-color: white;
        }
        .section-card:hover {
            transform: translateY(-5px);
        }
        .section-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .question-image {
            max-width: 100%;
            max-height: 300px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .audio-player {
            width: 100%;
            margin: 15px 0;
        }
        .question-media {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .navbar {
            background-color: rgba(255, 255, 255, 0.95) !important;
        }
        .options label {
            display: block;
            padding: 10px 15px;
            margin: 5px 0;
            background-color: #f8f9fa;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .options label:hover {
            background-color: #e9ecef;
        }
        .form-check-input:checked + label {
            background-color: #cfe2ff;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap me-2"></i>Quiz System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <?php if ($selected_section && !empty($questions)): ?>
    <div class="timer-container">
        <i class="fas fa-clock"></i>
        <p id="timer"></p>
    </div>
    <?php endif; ?>

    <div class="container">
        <?php if (!$selected_section): ?>
        <!-- Show sections when no section is selected -->
        <h2 class="mb-4">Select Quiz Section</h2>
        <div class="row">
            <?php while ($section = $sections_result->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
                <div class="card section-card" onclick="window.location.href='quiz.php?section=<?php echo $section['section_id']; ?>'">
                    <div class="card-body text-center">
                        <div class="section-icon">
                            <?php
                            $icon = 'question-circle';
                            switch(strtolower($section['section_name'])) {
                                case 'history':
                                    $icon = 'landmark';
                                    break;
                                case 'science':
                                    $icon = 'flask';
                                    break;
                                case 'games':
                                    $icon = 'gamepad';
                                    break;
                                case 'geography':
                                    $icon = 'globe';
                                    break;
                                case 'sports':
                                    $icon = 'football-ball';
                                    break;
                            }
                            ?>
                            <i class="fas fa-<?php echo $icon; ?>"></i>
                        </div>
                        <h4 class="card-title"><?php echo htmlspecialchars($section['section_name']); ?></h4>
                        <p class="card-text"><?php echo htmlspecialchars($section['section_description']); ?></p>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <!-- Show questions when section is selected -->
        <div class="mb-4">
            <a href="quiz.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Sections
            </a>
        </div>
        <?php if (empty($questions)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No questions available for this section yet.
            </div>
        <?php else: ?>
            <form id="quizForm" method="post" action="submit_quiz.php">
                <input type="hidden" name="section_id" value="<?php echo $selected_section; ?>">
                <?php foreach ($questions as $index => $question): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Question <?php echo $index + 1; ?></h5>
                        
                        <?php if (!empty($question['media_file'])): ?>
                        <div class="question-media">
                            <?php if ($question['question_type'] == 'image'): ?>
                            <img src="uploads/<?php echo htmlspecialchars($question['media_file']); ?>" 
                                 alt="Question Image" 
                                 class="question-image">
                            <?php elseif ($question['question_type'] == 'voice'): ?>
                            <audio controls class="audio-player">
                                <source src="uploads/<?php echo htmlspecialchars($question['media_file']); ?>" 
                                        type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <p class="card-text"><?php echo htmlspecialchars($question['quiz_question']); ?></p>
                        
                        <div class="options">
                            <?php foreach (['a', 'b', 'c', 'd'] as $key): ?>
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="radio" 
                                       name="answer[<?php echo $question['tbl_quiz_id']; ?>]" 
                                       id="q<?php echo $question['tbl_quiz_id']; ?>_<?php echo $key; ?>" 
                                       value="<?php echo htmlspecialchars($question['option_' . $key]); ?>"
                                       required>
                                <label class="form-check-label" for="q<?php echo $question['tbl_quiz_id']; ?>_<?php echo $key; ?>">
                                    <?php echo htmlspecialchars($question['option_' . $key]); ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-check-circle me-2"></i>Submit Quiz
                </button>
            </form>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($selected_section && !empty($questions)): ?>
    <script>
        // Timer functionality
        let timeLeft = 600; // 10 minutes in seconds
        let timerId = null;

        function startTimer() {
            timerId = setInterval(function() {
                timeLeft--;
                updateTimerDisplay();
                
                if (timeLeft <= 0) {
                    clearInterval(timerId);
                    document.getElementById('quizForm').submit();
                }
            }, 1000);
        }

        function updateTimerDisplay() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            document.getElementById('timer').textContent = display;
            
            // Change color when less than 1 minute remains
            if (timeLeft < 60) {
                document.getElementById('timer').style.color = 'red';
            }
        }

        // Start timer when page loads
        window.onload = function() {
            startTimer();
        }
    </script>
    <?php endif; ?>
</body>
</html>