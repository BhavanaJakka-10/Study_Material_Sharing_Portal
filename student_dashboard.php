<?php
session_start();

// Redirect if not logged in
if(!isset($_SESSION['student'])) {
    header("Location: student_login.php");
    exit();
}

include("db.php"); 

// $_SESSION['student'] should contain the ID of the student (e.g., 1)
$id = $_SESSION['student'];

// FIX: Changed 'student_id' to 'id' to match the table structure
$query = "SELECT name FROM student WHERE id='$id'";
$result = mysqli_query($conn, $query);

if($result && $row = mysqli_fetch_assoc($result)){
    $studentName = $row['name'];
} else {
    $studentName = "Student";
}

// Fetch counts for the stats section
// Added error suppression (@) or default 0 to prevent crashes if tables are empty
$paperResult = $conn->query("SELECT COUNT(*) as total FROM question_bank");
$paperCount = ($paperResult) ? mysqli_fetch_assoc($paperResult)['total'] : 0;

$materialResult = $conn->query("SELECT COUNT(*) as total FROM study_materials");
$materialCount = ($materialResult) ? mysqli_fetch_assoc($materialResult)['total'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Zeal EduHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3f37c9;
            --sidebar-bg: #0f172a;
            --bg-light: #f8fafc;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --glass: rgba(255, 255, 255, 0.9);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        
        body { background: var(--bg-light); color: var(--text-dark); overflow-x: hidden; }

        /* --- Header --- */
        .header {
            position: fixed; top: 0; left: 0; right: 0; height: 70px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 30px; z-index: 1001;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .logo { font-size: 22px; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: 10px; }
        .profile-area { display: flex; align-items: center; gap: 15px; background: #fff; padding: 5px 15px; border-radius: 50px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .profile-area span { font-weight: 600; font-size: 14px; }
        .profile-area img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; }

        /* --- Sidebar --- */
        .sidebar {
            position: fixed; left: 0; top: 70px; width: 260px; height: calc(100vh - 70px);
            background: var(--sidebar-bg); color: #fff; padding: 25px 15px;
            transition: 0.3s ease; z-index: 1000;
        }
        .sidebar ul { list-style: none; }
        .sidebar ul li { margin-bottom: 8px; }
        .sidebar ul li a {
            color: #94a3b8; text-decoration: none; padding: 12px 15px;
            display: flex; align-items: center; gap: 12px; border-radius: 10px;
            font-weight: 500; transition: 0.3s;
        }
        .sidebar ul li a:hover, .sidebar ul li.active a {
            background: rgba(255,255,255,0.1); color: #fff;
        }
        .sidebar ul li.active a { background: var(--primary); }

        /* --- Main Content --- */
        .main { margin-left: 260px; padding: 100px 40px 40px; min-height: 100vh; }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 24px; padding: 50px; color: #fff;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 20px 40px rgba(67, 97, 238, 0.2); margin-bottom: 35px;
        }
        .hero h1 { font-size: 32px; font-weight: 800; margin-bottom: 10px; }
        .hero p { opacity: 0.9; font-size: 16px; max-width: 500px; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 35px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 18px; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .stat-card h2 { font-size: 24px; font-weight: 700; }
        .stat-card p { color: var(--text-muted); font-size: 13px; font-weight: 500; }

        /* Feature Cards */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }
        .card {
            background: #fff; padding: 30px; border-radius: 22px; border: 1px solid #f1f5f9;
            transition: 0.3s; cursor: pointer; text-decoration: none; display: block;
        }
        .card:hover { transform: translateY(-8px); box-shadow: 0 20px 30px rgba(0,0,0,0.05); border-color: var(--primary); }
        .card i { font-size: 30px; color: var(--primary); margin-bottom: 20px; }
        .card h3 { color: var(--text-dark); margin-bottom: 10px; font-size: 18px; }
        .card p { color: var(--text-muted); font-size: 14px; line-height: 1.6; }

        /* Recent Activity */
        .recent-box { background: #fff; margin-top: 40px; padding: 30px; border-radius: 22px; border: 1px solid #f1f5f9; }
        .recent-box h2 { font-size: 20px; margin-bottom: 20px; }
        .activity-item { padding: 15px 0; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 15px; }
        .activity-item:last-child { border: none; }
        .dot { height: 8px; width: 8px; background: var(--primary); border-radius: 50%; }

        /* Footer */
        .footer { margin-left: 260px; padding: 30px; text-align: center; color: var(--text-muted); font-size: 14px; border-top: 1px solid #e2e8f0; }

        @media (max-width: 992px) {
            .sidebar { left: -260px; }
            .main, .footer { margin-left: 0; padding: 100px 20px 20px; }
            .hero { flex-direction: column; text-align: center; padding: 30px; }
            .hero img { display: none; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="logo"><i class="fa-solid fa-graduation-cap"></i>ZEALHUB</div>
    <div class="profile-area">
        <span>Hi, <?= htmlspecialchars($studentName) ?></span>
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profile">
    </div>
</header>

<aside class="sidebar">
    <ul>
        <li class="active"><a href="student_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
        <li><a href="profile.php"><i class="fa-solid fa-user"></i> My Profile</a></li>
        <li><a href="student_questionbank.php"><i class="fa-solid fa-file-pdf"></i> Question Bank</a></li>
        <li><a href="student_syllabus.php"><i class="fa-solid fa-book"></i> Syllabus</a></li>
        <li><a href="student_study_material.php" onclick="comingSoon()"><i class="fa-solid fa-book"></i> Study Material</a></li>
         <li><a href="student_raise_query.php"><i class="fa-solid fa-user"></i>  Raise query</a></li>
        <li><a href="student_lab.php" onclick="comingSoon()"><i class="fa-solid fa-laptop-code"></i> Lab Practice</a></li>
        <li><a href="student_logout.php" style="color: #f87171;"><i class="fa-solid fa-power-off"></i> Logout</a></li>
    </ul>
</aside>

<main class="main">
    <section class="hero">
        <div>
            <h1>Welcome Back, <?= htmlspecialchars($studentName) ?>! 👋</h1>
            <p>Your centralized portal for study materials, syllabus updates, and university previous year question papers.</p>
        </div>
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135755.png" width="140" style="filter: brightness(0) invert(1);">
    </section>

    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #e0e7ff; color: #4361ee;"><i class="fa-solid fa-file-lines"></i></div>
            <div>
                <h2><?= $paperCount ?></h2>
                <p>Question Papers</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fef3c7; color: #d97706;"><i class="fa-solid fa-book"></i></div>
            <div>
                <h2><?= $materialCount ?></h2>
                <p>Study Notes</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #dcfce7; color: #16a34a;"><i class="fa-solid fa-check-double"></i></div>
            <div>
                <h2>100%</h2>
                <p>Verified Content</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fee2e2; color: #dc2626;"><i class="fa-solid fa-clock"></i></div>
            <div>
                <h2>24/7</h2>
                <p>Access</p>
            </div>
        </div>
    </section>

    <div class="grid">
        <a href="student_questionbank.php" class="card">
            <i class="fa-solid fa-circle-question"></i>
            <h3>Question Bank</h3>
            <p>Access subject-wise important questions and model papers for exam prep.</p>
        </a>

        <div class="card" onclick="comingSoon()">
            <i class="fa-solid fa-book-open"></i>
            <h3>Study Material</h3>
            <p>Download PDFs, PPTs, and handwritten notes uploaded by the faculty.</p>
        </div>

        <div class="card" onclick="comingSoon()">
            <i class="fa-solid fa-scroll"></i>
            <h3>Syllabus</h3>
            <p>Stay updated with the latest University curriculum and course outcomes.</p>
        </div>
    </div>

    <div class="recent-box">
        <h2><i class="fa-solid fa-clock-rotate-left"></i> Recent Portal Updates</h2>
        <div class="activity-item">
            <div class="dot"></div>
            <span>New <b>Database Management</b> papers added to Question Bank.</span>
        </div>
        <div class="activity-item">
            <div class="dot"></div>
            <span><b>Java Programming</b> Unit-3 notes are now available.</span>
        </div>
        <div class="activity-item">
            <div class="dot"></div>
            <span>System Maintenance scheduled for Sunday at 10:00 PM.</span>
        </div>
    </div>
</main>

<footer class="footer">
    <p>© 2026 Zeal EduHub - Department of Information Technology</p>
</footer>

<script>
function comingSoon() {
    Swal.fire({
        title: 'Feature Coming Soon',
        text: 'This module is currently being updated by our IT team. Please check back later!',
        icon: 'info',
        confirmButtonColor: '#4361ee'
    });
}
</script>

</body>
</html>