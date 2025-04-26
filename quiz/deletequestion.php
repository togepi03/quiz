<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: loginquiz.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['question_id'])) {
        $questionID = $_GET['question_id'];
        $stmt = $conn->prepare("DELETE FROM tbl_quiz WHERE tbl_quiz_id = ?");
        $stmt->bind_param("i", $questionID);
        
        if ($stmt->execute()) {
            $message = "Question deleted successfully";
            header("Location: home.php?message=" . urlencode($message));
        } else {
            $message = "Error deleting question: " . $conn->error;
        }
        $stmt->close();
    } 
    elseif (isset($_GET['section_id'])) {
        $sectionID = $_GET['section_id'];
        
        $conn->begin_transaction();
        
        try {
            $stmt = $conn->prepare("DELETE FROM tbl_quiz WHERE section_id = ?");
            $stmt->bind_param("i", $sectionID);
            $stmt->execute();
            $stmt->close();
            
            $stmt = $conn->prepare("DELETE FROM tbl_sections WHERE section_id = ?");
            $stmt->bind_param("i", $sectionID);
            $stmt->execute();
            $stmt->close();
            
            $conn->commit();
            $message = "Section and its questions deleted successfully";
            header("Location: home.php?message=" . urlencode($message));
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error deleting section: " . $e->getMessage();
        }
    } else {
        $message = "Invalid request. Missing ID.";
    }
}

if ($message) {
    header("Location: home.php?error=" . urlencode($message));
}
exit();
?>
