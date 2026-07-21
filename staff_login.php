<?php
session_start();
include("db.php");

$error = "";

// Initialize login attempts
if (!isset($_SESSION['staff_login_attempts'])) {
    $_SESSION['staff_login_attempts'] = 0;
}

// If attempts reach 5, redirect
if ($_SESSION['staff_login_attempts'] >= 5) {
    header("Location: staff_forgot_password.php");
    exit();
}

if (isset($_POST['login'])) {
    // Sanitize inputs to prevent SQL Injection
    $id = mysqli_real_escape_string($conn, trim($_POST['staffid']));
    $pass = mysqli_real_escape_string($conn, trim($_POST['password']));

    $sql = "SELECT * FROM staff_profile WHERE email='$id' AND password='$pass'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['staff_login_attempts'] = 0;
        $_SESSION['staff'] = $row['staff_id'];
        $_SESSION['staff_name'] = $row['name'];
        header("Location: staff_dashboard.php");
        exit();
    } else {
        $_SESSION['staff_login_attempts']++;
        $remaining = 5 - $_SESSION['staff_login_attempts'];
        if ($_SESSION['staff_login_attempts'] >= 5) {
            header("Location: staff_forgot_password.php");
            exit();
        } else {
            $error = "Invalid credentials. <b>$remaining</b> attempts remaining.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login | Exam Portal</title>
    <!-- Google Fonts -->
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
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: var(--bg-gradient);
            padding: 20px;
        }

        .container {
            width: 1000px;
            height: 600px;
            background: var(--glass-bg);
            display: flex;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* Left Branding Section */
        .left {
            width: 45%;
            background: linear-gradient(225deg, #6366f1 0%, #4338ca 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            position: relative;
        }

        .left h1 { font-size: 38px; font-weight: 800; margin-bottom: 15px; letter-spacing: -1px; }
        .left h2 { font-size: 24px; font-weight: 400; opacity: 0.9; margin-bottom: 30px; }
        
        .feature-list { list-style: none; margin-bottom: 40px; }
        .feature-list li { 
            display: flex; align-items: center; gap: 12px; 
            margin-bottom: 15px; font-size: 15px; opacity: 0.85;
        }

        .left img {
            width: 180px;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.2));
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Right Login Section */
        .right {
            width: 55%;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
        }

        .login-header { text-align: center; margin-bottom: 35px; }
        
        .icon-shield {
            width: 70px; height: 70px; background: #eef2ff;
            color: var(--primary); border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 30px; margin: 0 auto 20px;
        }

        .login-header h2 { font-size: 28px; color: var(--text-main); font-weight: 700; }
        .login-header p { color: var(--text-muted); font-size: 14px; }

        /* Form Controls */
        .input-group { position: relative; margin-bottom: 20px; }
        .input-group i {
            position: absolute; left: 18px; top: 50%;
            transform: translateY(-50%); color: #94a3b8;
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
        }

        .input-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .input-group input:focus + i { color: var(--primary); }

        .helper-row {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 30px; font-size: 14px;
        }

        .helper-row a { text-decoration: none; color: var(--primary); font-weight: 600; }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        }

        .btn-login:hover { background: var(--primary-dark); transform: translateY(-2px); }

        .error-msg {
            background: #fff1f2; color: #e11d48;
            padding: 14px; border-radius: 12px;
            margin-bottom: 20px; font-size: 14px;
            display: flex; align-items: center; gap: 10px;
            border: 1px solid #ffe4e6;
        }

        .lock-card { text-align: center; padding: 20px; }
        .lock-card i { font-size: 50px; color: #e11d48; margin-bottom: 15px; }

        .footer { margin-top: 40px; text-align: center; color: #94a3b8; font-size: 13px; }

        @media (max-width: 900px) {
            .container { flex-direction: column; height: auto; width: 100%; }
            .left, .right { width: 100%; padding: 40px 30px; }
            .left { text-align: center; }
            .feature-list { display: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Left Branding -->
    <div class="left">
        <h1>EXAM PORTAL</h1>
        <h2>Staff Administration</h2>
        
        <ul class="feature-list">
            <li><i class="fa-solid fa-circle-check"></i> Manage Question Banks</li>
            <li><i class="fa-solid fa-circle-check"></i> Track Portal Activity</li>
            <li><i class="fa-solid fa-circle-check"></i> Student Query Resolution</li>
        </ul>

        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Staff Icon">
    </div>

    <!-- Right Form -->
    <div class="right">
        <div class="login-header">
            <div class="icon-shield">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <h2>Welcome Back</h2>
            <p>Secure access for academic personnel</p>
        </div>

        <?php if($error != ""): ?>
            <div class="error-msg">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <?php if($_SESSION['staff_login_attempts'] < 5): ?>
            <form method="POST">
                <div class="input-group">
                    <input type="email" name="staffid" placeholder="Official Email Address" required>
                    <i class="fa-solid fa-envelope"></i>
                </div>

                <div class="input-group">
                    <input type="password" name="password" placeholder="Account Password" required>
                    <i class="fa-solid fa-lock"></i>
                </div>

                <div class="helper-row">
                    <label style="display: flex; align-items: center; gap: 8px; color: var(--text-muted);">
                        <input type="checkbox" style="accent-color: var(--primary);"> Remember me
                    </label>
                    <a href="staff_forget_password.php">Forgot Password?</a>
                </div>

                <button type="submit" name="login" class="btn-login">
                    Sign In to Portal
                </button>
            </form>
        <?php else: ?>
            <div class="lock-card">
                <i class="fa-solid fa-user-lock"></i>
                <h3 style="color: #1e293b; margin-bottom: 10px;">Account Temporarily Locked</h3>
                <p style="color: #64748b; font-size: 14px;">Maximum login attempts exceeded for security reasons.</p>
                <a href="staff_forget_password.php" style="text-decoration: none;">
                    <button type="button" class="btn-login" style="margin-top: 20px; background: #ef4444;">
                        Verify Identity & Unlock
                    </button>
                </a>
            </div>
        <?php endif; ?>

        <div class="footer">
            &copy; 2026 Examination Portal • Authorization Required
        </div>
    </div>
</div>

</body>
</html>