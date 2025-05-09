<?php
session_start();
require_once 'db_connection.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Administrator') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $userID = trim($_POST['userID']);
    $email = trim($_POST['email']);
    $newPassword = trim($_POST['newPassword']);
    $confirmPassword = trim($_POST['confirmPassword']);
    $requestID = isset($_POST['requestID']) ? trim($_POST['requestID']) : null;
    $sendEmail = isset($_POST['sendEmail']) ? true : false;
    
    // Validate data
    if (empty($userID) || empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit();
    }
    
    if (strlen($newPassword) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
        exit();
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update user password
        $updatePasswordSQL = "UPDATE users SET password = ? WHERE userID = ?";
        $stmt = $conn->prepare($updatePasswordSQL);
        $stmt->bind_param("ss", $hashedPassword, $userID);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update password");
        }
        
        // If this was a password reset request, update its status
        if ($requestID) {
            $updateRequestSQL = "UPDATE password_reset_requests SET status = 'completed', completed_date = NOW(), completed_by = ? WHERE request_id = ?";
            $stmt = $conn->prepare($updateRequestSQL);
            $stmt->bind_param("si", $_SESSION['userID'], $requestID);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update request status");
            }
        }
        
        // Log the password reset action
        $logSQL = "INSERT INTO admin_logs (admin_id, action, affected_user, action_details, ip_address) 
                  VALUES (?, 'password_reset', ?, 'Reset user password', ?)";
        $stmt = $conn->prepare($logSQL);
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $stmt->bind_param("sss", $_SESSION['userID'], $userID, $ipAddress);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to log action");
        }
        
        // Send email notification if requested
        if ($sendEmail && !empty($email)) {
            if (!sendPasswordResetEmail($email, $userID, $newPassword)) {
                // Don't fail the entire operation if email fails, just log it
                $logEmailFailSQL = "INSERT INTO admin_logs (admin_id, action, affected_user, action_details, ip_address) 
                                   VALUES (?, 'email_fail', ?, 'Failed to send password reset email', ?)";
                $stmt = $conn->prepare($logEmailFailSQL);
                $stmt->bind_param("sss", $_SESSION['userID'], $userID, $ipAddress);
                $stmt->execute();
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Password has been reset successfully']);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    } finally {
        $conn->close();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

/**
 * Sends password reset email to user
 * 
 * @param string $email User's email address
 * @param string $userID User's ID
 * @param string $password New password
 * @return bool True if email sent successfully, false otherwise
 */
function sendPasswordResetEmail($email, $userID, $password) {
    // Attempt to load PHPMailer if installed
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        // PHPMailer not available, use simple mail function
        $subject = "Your Password Has Been Reset - Roti Seri Bakery";
        
        $message = "Dear User,\n\n";
        $message .= "Your password for the RotiSeri Bakery Inventory System has been reset by an administrator.\n\n";
        $message .= "Your new login details are:\n";
        $message .= "User ID: " . $userID . "\n";
        $message .= "Password: " . $password . "\n\n";
        $message .= "Please change your password after logging in for security reasons.\n\n";
        $message .= "Regards,\nRotiSeri Bakery Admin Team";
        
        $headers = "From: admin@rotiseribakery.com\r\n";
        $headers .= "Reply-To: admin@rotiseribakery.com\r\n";
        
        return mail($email, $subject, $message, $headers);
    } else {
        // Use PHPMailer if available
        require 'vendor/autoload.php'; // Assumes Composer is used
        
        try {
            $mail = new PHPMailer(true);
            
// Server settings
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
$mail->SMTPAuth = true;
$mail->Username = 'ahwljnbby1411@gmail.com'; // Your Gmail address
$mail->Password = 'your-app-password'; // Your Gmail app password
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

// Add this line for debugging if needed
$mail->SMTPDebug = 0; // Set to 2 for detailed debugging

// Recipients
$mail->setFrom('ahwljnbby1411@gmail.com', 'Roti Seri Bakery Admin');
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your Password Has Been Reset - Roti Seri Bakery';
            
            $htmlMessage = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                    .header { background-color: #0561FC; color: white; padding: 10px; text-align: center; }
                    .content { padding: 20px; }
                    .footer { font-size: 12px; color: #777; margin-top: 20px; }
                    .credentials { background-color: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 5px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Password Reset Notification</h2>
                    </div>
                    <div class='content'>
                        <p>Dear User,</p>
                        <p>Your password for the RotiSeri Bakery Inventory System has been reset by an administrator.</p>
                        <div class='credentials'>
                            <p><strong>Your new login details are:</strong></p>
                            <p>User ID: {$userID}</p>
                            <p>Password: {$password}</p>
                        </div>
                        <p>Please change your password after logging in for security reasons.</p>
                        <p>If you did not request this password reset, please contact the administrator immediately.</p>
                    </div>
                    <div class='footer'>
                        <p>Regards,<br>RotiSeri Bakery Admin Team</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $textMessage = "Dear User,\n\n";
            $textMessage .= "Your password for the RotiSeri Bakery Inventory System has been reset by an administrator.\n\n";
            $textMessage .= "Your new login details are:\n";
            $textMessage .= "User ID: " . $userID . "\n";
            $textMessage .= "Password: " . $password . "\n\n";
            $textMessage .= "Please change your password after logging in for security reasons.\n\n";
            $textMessage .= "If you did not request this password reset, please contact the administrator immediately.\n\n";
            $textMessage .= "Regards,\nRotiSeri Bakery Admin Team";
            
            $mail->Body = $htmlMessage;
            $mail->AltBody = $textMessage;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>