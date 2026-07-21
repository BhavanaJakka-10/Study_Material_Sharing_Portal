<?php
session_start();

$message = "";
$error = "";
$idVerified = false;

// Simulated Database (In a real app, this would be a SQL query)
$validStudentId = "123";

// Logic Phase
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Step 1: Verify ID
    if (isset($_POST['verify_id'])) {
        $id = trim($_POST['studentid']);
        if ($id === $validStudentId) {
            $idVerified = true;
            $_SESSION['temp_id'] = $id; // Store in session to remember during next post
        } else {
            $error = "Student ID not found in our records.";
        }
    }

    // Step 2: Reset Password
    if (isset($_POST['reset_password'])) {
        $idVerified = true; // Keep fields visible
        $newPass = $_POST['newpassword'];
        $confPass = $_POST['confirmpassword'];

        if (strlen($newPass) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif ($newPass !== $confPass) {
            $error = "Passwords do not match!";
        } else {
            // Success logic
            $_SESSION['student_password'] = $newPass;
            $_SESSION['login_attempts'] = 0;
            $message = "Password updated successfully!";
            $idVerified = false; // Reset view
            unset($_SESSION['temp_id']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Password Reset</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass: rgba(255, 255, 255, 0.95);
            --error-bg: #fee2e2;
            --error-text: #dc2626;
            --success-bg: #dcfce7;
            --success-text: #16a34a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .card {
            background: var(--glass);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            backdrop-filter: blur(10px);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            color: #1f2937;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .header p {
            color: #6b7280;
            font-size: 0.875rem;
            margin-top: 8px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.2s;
            outline: none;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        button {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }

        button:hover {
            background: var(--primary-hover);
        }

        button:active {
            transform: scale(0.98);
        }

        .alert {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.875rem;
            text-align: center;
            animation: fadeIn 0.3s ease;
        }

        .alert-error { background: var(--error-bg); color: var(--error-text); }
        .alert-success { background: var(--success-bg); color: var(--success-text); }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            text-decoration: none;
            color: #6b7280;
            font-size: 0.875rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--primary);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .id-verified-badge {
            background: #e0e7ff;
            color: #4338ca;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="header">
        <h2>Reset Password</h2>
        <p>Follow the steps to secure your account</p>
    </div>

    <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        
        <?php if(!$idVerified): ?>
            <!-- Step 1 View -->
            <div class="input-group">
                <label>Student Identity</label>
                <input type="text" name="studentid" placeholder="Enter your ID " required autofocus>
            </div>
            <button type="submit" name="verify_id">Verify Identity</button>
        <?php else: ?>
            <!-- Step 2 View -->
            <div class="id-verified-badge">✓ ID Verified: <?php echo htmlspecialchars($_SESSION['temp_id']); ?></div>
            
            <div class="input-group">
                <label>New Password</label>
                <input type="password" name="newpassword" placeholder="••••••••" required autofocus>
            </div>

            <div class="input-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirmpassword" placeholder="••••••••" required>
            </div>
            
            <button type="submit" name="reset_password">Update Password</button>
        <?php endif; ?>

    </form>

    <a href="student_login.php" class="back-link">← Back to Login</a>
</div>

</body>
</html>