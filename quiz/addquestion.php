<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: loginquiz.php");
    exit();
}

include("conn.php");

$message = '';
$messageType = '';

if (isset($_POST['add_section'])) {
    $sectionName = trim($_POST['section_name']);
    $sectionDesc = trim($_POST['section_description']);
    
    if (!empty($sectionName)) {
        $stmt = $conn->prepare("INSERT INTO tbl_sections (section_name, section_description) VALUES (?, ?)");
        $stmt->bind_param("ss", $sectionName, $sectionDesc);
        
        if ($stmt->execute()) {
            $message = "New section added successfully!";
            $messageType = "success";
        } else {
            $message = "Error adding section: " . $stmt->error;
            $messageType = "danger";
        }
        $stmt->close();
    }
}

if (isset($_POST['add_question'])) {
    $questionType = $_POST['question_type'];
    $sectionId = trim($_POST['section_id']);
    $quizQuestion = trim($_POST['quiz_question']);
    $optionA = trim($_POST['option_a']);
    $optionB = trim($_POST['option_b']);
    $optionC = trim($_POST['option_c']);
    $optionD = trim($_POST['option_d']);
    $correctAnswer = trim($_POST['correct_answer']);
    
    $mediaFile = null;
    $mediaType = null;
    if ($questionType !== 'text') {
        if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] == 0) {
            $allowedImageTypes = [
                'image/jpeg',
                'image/jpg',
                'image/pjpeg',
                'image/png',
                'image/gif'
            ];
            $allowedVoiceTypes = [
                'audio/mpeg',
                'audio/mp3',
                'audio/wav',
                'audio/wave',
                'audio/x-wav',
                'audio/ogg',
                'audio/vorbis'
            ];
            $maxFileSize = 5 * 1024 * 1024; 
            
            $file = $_FILES['media_file'];
            $fileType = strtolower($file['type']);
            $fileSize = $file['size'];
            
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $validImageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $validVoiceExtensions = ['mp3', 'wav', 'ogg'];
            
            if ($questionType == 'image') {
                if (!in_array($fileType, $allowedImageTypes) && !in_array($extension, $validImageExtensions)) {
                    $message = "Invalid image format. Allowed formats: JPG, PNG, GIF";
                    $messageType = "danger";
                }
            } elseif ($questionType == 'voice') {
                if (!in_array($fileType, $allowedVoiceTypes) && !in_array($extension, $validVoiceExtensions)) {
                    $message = "Invalid audio format. Allowed formats: MP3, WAV, OGG";
                    $messageType = "danger";
                }
            }
            
            if (empty($message) && $fileSize > $maxFileSize) {
                $message = "File size too large. Maximum size: 5MB";
                $messageType = "danger";
            }
            
            if (empty($message)) {
                $extension = $extension ?: pathinfo($file['name'], PATHINFO_EXTENSION);
                $newFileName = uniqid() . '.' . $extension;
                $uploadPath = 'uploads/' . $newFileName;
                
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $mediaFile = $newFileName;
                    $mediaType = $questionType;
                } else {
                    $message = "Error uploading file. Please check file permissions.";
                    $messageType = "danger";
                }
            }
        } else {
            $message = "Please select a file to upload";
            $messageType = "danger";
        }
    }
    
    if (empty($message)) {
        $stmt = $conn->prepare("INSERT INTO tbl_quiz (quiz_question, option_a, option_b, option_c, option_d, correct_answer, section_id, question_type, media_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("sssssssss", $quizQuestion, $optionA, $optionB, $optionC, $optionD, $correctAnswer, $sectionId, $questionType, $mediaFile);
            
            if ($stmt->execute()) {
                $message = "Question added successfully!";
                $messageType = "success";
                $_POST = array();
            } else {
                $message = "Error: " . $stmt->error;
                $messageType = "danger";
            }
            $stmt->close();
        } else {
            $message = "Error: " . $conn->error;
            $messageType = "danger";
        }
    }
}

$sections = $conn->query("SELECT * FROM tbl_sections ORDER BY section_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Question - Quiz System</title>
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
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
        }
        #audioPreview {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-folder-plus me-2"></i>Add New Section</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="section_name" class="form-label">Section Name</label>
                                <input type="text" class="form-control" id="section_name" name="section_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="section_description" class="form-label">Description</label>
                                <textarea class="form-control" id="section_description" name="section_description" rows="3"></textarea>
                            </div>
                            <button type="submit" name="add_section" class="btn btn-success">
                                <i class="fas fa-plus-circle me-2"></i>Add Section
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>Add New Question</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="section_id" class="form-label">Select Section</label>
                                <select class="form-select" id="section_id" name="section_id" required>
                                    <option value="">Choose a section...</option>
                                    <?php while ($section = $sections->fetch_assoc()): ?>
                                    <option value="<?php echo $section['section_id']; ?>">
                                        <?php echo htmlspecialchars($section['section_name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="question_type" class="form-label">Question Type</label>
                                <select class="form-select" id="question_type" name="question_type" required>
                                    <option value="text">Text Only</option>
                                    <option value="image">Image Question</option>
                                    <option value="voice">Voice Question</option>
                                </select>
                            </div>

                            <div id="mediaUpload" class="mb-3" style="display: none;">
                                <label for="media_file" class="form-label">Upload File</label>
                                <input type="file" class="form-control" id="media_file" name="media_file">
                                <div id="mediaPreview" class="mt-2"></div>
                            </div>

                            <div class="mb-3">
                                <label for="quiz_question" class="form-label">Question</label>
                                <textarea class="form-control" id="quiz_question" name="quiz_question" rows="3" required></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="option_a" class="form-label">Option A</label>
                                    <input type="text" class="form-control" id="option_a" name="option_a" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="option_b" class="form-label">Option B</label>
                                    <input type="text" class="form-control" id="option_b" name="option_b" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="option_c" class="form-label">Option C</label>
                                    <input type="text" class="form-control" id="option_c" name="option_c" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="option_d" class="form-label">Option D</label>
                                    <input type="text" class="form-control" id="option_d" name="option_d" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="correct_answer" class="form-label">Correct Answer</label>
                                <select class="form-select" id="correct_answer" name="correct_answer" required>
                                    <option value="">Select correct answer...</option>
                                    <option value="a">Option A</option>
                                    <option value="b">Option B</option>
                                    <option value="c">Option C</option>
                                    <option value="d">Option D</option>
                                </select>
                            </div>

                            <button type="submit" name="add_question" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>Add Question
                            </button>
                            <a href="home.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('question_type').addEventListener('change', function() {
            const mediaUpload = document.getElementById('mediaUpload');
            const mediaFile = document.getElementById('media_file');
            const mediaPreview = document.getElementById('mediaPreview');
            
            if (this.value === 'text') {
                mediaUpload.style.display = 'none';
                mediaFile.removeAttribute('required');
            } else {
                mediaUpload.style.display = 'block';
                mediaFile.setAttribute('required', 'required');
                
                if (this.value === 'image') {
                    mediaFile.accept = 'image/*';
                } else if (this.value === 'voice') {
                    mediaFile.accept = 'audio/*';
                }
            }
        });

        document.getElementById('media_file').addEventListener('change', function(e) {
            const mediaPreview = document.getElementById('mediaPreview');
            const questionType = document.getElementById('question_type').value;
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    mediaPreview.innerHTML = '';
                    
                    if (questionType === 'image') {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'preview-image';
                        mediaPreview.appendChild(img);
                    } else if (questionType === 'voice') {
                        const audio = document.createElement('audio');
                        audio.src = e.target.result;
                        audio.controls = true;
                        audio.id = 'audioPreview';
                        mediaPreview.appendChild(audio);
                    }
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>
</body>
</html>
