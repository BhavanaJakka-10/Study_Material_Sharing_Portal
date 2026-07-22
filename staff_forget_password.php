<?php
session_start();
include("db.php");
include("SmtpMailer.php");

$message = ""; 
$msg_type = "";
date_default_timezone_set('Asia/Kolkata');

// --- SMTP CONFIGURATION CREDENTIALS ---
// Replace with your actual SMTP server credentials (e.g. Gmail App Password)
$smtp_host = "smtp.gmail.com";
$smtp_port = 465; // 465 for SSL or 587 for TLS
$smtp_user = "your-email@gmail.com"; // Your Sender Email
$smtp_pass = "your-app-password";    // Your App Password / Email Password

if (isset($_POST['reset_request'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));

    // Check if staff email exists
    $stmt = $conn->prepare("SELECT id, name FROM staff_profile WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $staff = $result->fetch_assoc();
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+30 minutes"));

        // Store reset token & expiry in staff_profile table
        $update_stmt = $conn->prepare("UPDATE staff_profile SET reset_token = ?, token_expiry = ? WHERE email = ?");
        $update_stmt->bind_param("sss", $token, $expiry, $email);
        $update_stmt->execute();

        // Build dynamic reset password URL
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $reset_link = "$protocol://$host$dir/staff_reset_password.php?token=" . $token;

        // Build HTML Email Body
        $subject = "Password Reset Request - Staff Portal";
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;'>
            <h2 style='color: #4f46e5; margin-bottom: 10px;'>Password Reset Request</h2>
            <p style='color: #334155; font-size: 15px;'>Hello " . htmlspecialchars($staff['name']) . ",</p>
            <p style='color: #475569; font-size: 14px; line-height: 1.6;'>
                We received a request to reset your password for the Staff Portal. Click the button below to set up a new password. This link is valid for <strong>30 minutes</strong>.
            </p>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='$reset_link' style='background-color: #4f46e5; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; font-size: 15px;'>Reset Password</a>
            </div>
            <p style='color: #64748b; font-size: 13px;'>If you did not request a password reset, please ignore this email or contact the administrator.</p>
            <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
            <p style='color: #94a3b8; font-size: 12px; text-align: center;'>Staff Administration Portal • Secured Email System</p>
        </div>";

        $smtp = new SmtpMailer($smtp_host, $smtp_port, $smtp_user, $smtp_pass);
        
        if ($smtp->send($email, $subject, $body)) {
            $message = "A password reset link has been successfully sent to <strong>" . htmlspecialchars($email) . "</strong>. Please check your inbox.";
            $msg_type = "success";
        } else {
            // Fallback message with debug simulation link for local testing
            $message = "Unable to connect to SMTP server. If SMTP credentials are not configured yet, you can use the debug link below:<br><br><a href='$reset_link' style='color:#4f46e5; font-weight:bold;' target='_blank'>[Click here to open Reset Link directly]</a>";
            $msg_type = "warning";
        }
    } else {
        $message = "No staff account found with that email address.";
        $msg_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Staff Portal</title>
    <!-- Google Fonts & FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: var(--bg-gradient);
            padding: 20px;
        }

        .card {
            background: var(--glass-bg);
            padding: 45px 40px;
            border-radius: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 440px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .icon-key {
            width: 70px;
            height: 70px;
            background: #eef2ff;
            color: var(--primary);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 20px;
        }

        h2 {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        p.subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            transition: 0.3s;
        }

        .input-group input {
            width: 100%;
            padding: 16px 16px 16px 52px;
            border: 2px solid #f1f5f9;
            border-radius: 16px;
            font-size: 15px;
            font-weight: 500;
            transition: 0.3s;
            outline: none;
            color: var(--text-main);
            background: #fff;
        }

        .input-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .input-group input:focus + i {
            color: var(--primary);
        }

        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 16px;
            width: 100%;
            border-radius: 16px;
            cursor: pointer;
            font-weight: 700;
            font-size: 16px;
            transition: 0.3s;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .alert {
            padding: 14px 16px;
            margin-bottom: 20px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 500;
            text-align: left;
            line-height: 1.5;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bcf0da; }
        .alert-warning { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 25px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: 0.2s;
        }

        .back-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-key">
            <i class="fa-solid fa-key"></i>
        </div>
        <h2>Forgot Password?</h2>
        <p class="subtitle">Enter your official staff email address to receive a secure password reset link.</p>

        <?php if($message != ""): ?>
            <div class="alert alert-<?php echo $msg_type; ?>">
                <i class="fa-solid <?php echo ($msg_type == 'success') ? 'fa-circle-check' : (($msg_type == 'warning') ? 'fa-triangle-exclamation' : 'fa-circle-exclamation'); ?>" style="margin-top:2px;"></i>
                <div><?php echo $message; ?></div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <input type="email" name="email" placeholder="staff@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                <i class="fa-solid fa-envelope"></i>
            </div>
            <button type="submit" name="reset_request" class="btn">
                <i class="fa-solid fa-paper-plane"></i> Send Reset Link
            </button>
        </form>

        <a href="staff_login.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Login
        </a>
    </div>
</body>
</html>
