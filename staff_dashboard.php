<?php
session_start();
include("db.php");

// Security Check
if(!isset($_SESSION['staff'])){
    header("Location: staff_login.php");
    exit();
}

$staffID = $_SESSION['staff'];
$staffDisplayName = isset($_SESSION['staff_name']) ? $_SESSION['staff_name'] : $staffID;

// --- DATABASE LOGIC: Real-time Stats ---
$q_res = $conn->query("SELECT COUNT(*) as total FROM student_queries");
$total_queries = ($q_res) ? $q_res->fetch_assoc()['total'] : 0;

$qb_res = $conn->query("SELECT COUNT(*) as total FROM question_bank");
$total_qb = ($qb_res) ? $qb_res->fetch_assoc()['total'] : 0;

$sm_res = $conn->query("SELECT COUNT(*) as total FROM study_materials");
$total_sm = ($sm_res) ? $sm_res->fetch_assoc()['total'] : 0;

$st_res = $conn->query("SELECT COUNT(*) as total FROM students");
$total_students = ($st_res) ? $st_res->fetch_assoc()['total'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ZEALHUB</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* --- THEME VARIABLES --- */
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --bg: #f8fafc;
            --header-bg: rgba(255, 255, 255, 0.9);
            --sidebar-bg: #ffffff;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /* --- DARK MODE OVERRIDES --- */
        [data-theme="dark"] {
            --bg: #0f172a;
            --header-bg: rgba(15, 23, 42, 0.9);
            --sidebar-bg: #1e293b;
            --card-bg: #1e293b;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border: #334155;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; transition: background 0.3s, color 0.3s; }

        body { background: var(--bg); color: var(--text-main); overflow: hidden; }

        /* --- HEADER --- */
        .header {
            height: 75px; background: var(--header-bg); backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border); display: flex; justify-content: space-between;
            align-items: center; padding: 0 25px; position: fixed; top: 0; width: 100%; z-index: 1000;
        }

        .header-left { display: flex; align-items: center; gap: 20px; }
        
        .menu-toggle, .theme-toggle {
            background: var(--primary); color: white; border: none;
            width: 40px; height: 40px; border-radius: 10px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; font-size: 18px;
        }

        .logo { font-size: 22px; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: 10px; }

        .header-right { display: flex; align-items: center; gap: 15px; }

        /* Profile Section */
        .profile-section {
            display: flex; align-items: center; gap: 12px; padding: 6px 12px;
            border-radius: 12px; border: 1px solid var(--border); background: var(--card-bg);
            text-decoration: none; color: inherit;
        }
        .profile-img { width: 32px; height: 32px; border-radius: 50%; }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 280px; height: 100vh; background: var(--sidebar-bg);
            border-right: 1px solid var(--border); position: fixed; top: 75px;
            left: 0; transition: 0.4s; padding: 20px 15px; z-index: 999;
        }
        .sidebar.collapsed { width: 80px; }
        .sidebar.collapsed .link-text { display: none; }

        .sidebar a {
            display: flex; align-items: center; gap: 15px; padding: 12px 18px;
            text-decoration: none; color: var(--text-muted); font-weight: 600;
            border-radius: 12px; margin-bottom: 8px;
        }
        .sidebar a:hover, .sidebar a.active { background: var(--bg); color: var(--primary); }
        .sidebar a.active { background: var(--primary); color: white; }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: 280px; margin-top: 75px; padding: 30px;
            height: calc(100vh - 75px); overflow-y: auto;
        }
        .main-content.expanded { margin-left: 80px; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .stat-card {
            background: var(--card-bg); padding: 25px; border-radius: 24px;
            border: 1px solid var(--border); display: flex; align-items: center; gap: 20px;
            text-decoration: none; color: inherit; transition: 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); border-color: var(--primary); box-shadow: var(--shadow); }
        .icon-box { width: 55px; height: 55px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 22px; }

        /* Info Section */
        .info-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
        .content-card { background: var(--card-bg); padding: 30px; border-radius: 24px; border: 1px solid var(--border); }

        .task-item {
            display: flex; align-items: center; gap: 12px; padding: 15px;
            background: var(--bg); border-radius: 12px; margin-bottom: 10px;
            text-decoration: none; color: var(--text-main); font-weight: 600; border: 1px solid transparent;
        }
        .task-item:hover { border-color: var(--primary); background: var(--card-bg); transform: translateX(5px); }

        /* About Us */
        .about-us { background: #1e293b; color: #f8fafc; padding: 40px; border-radius: 24px; margin-top: 40px; }
        .about-us h2 { color: var(--primary); margin-bottom: 15px; }

    </style>
</head>
<body data-theme="light">

    <header class="header">
        <div class="header-left">
            <button class="menu-toggle" id="toggleBtn"><i class="fa-solid fa-bars-staggered"></i></button>
            <div class="logo"><i class="fa-solid fa-bolt"></i> ZEALHUB</div>
        </div>
        
        <div class="header-right">
            <!-- THEME TOGGLE BUTTON -->
            <button class="theme-toggle" id="themeBtn" title="Switch Theme">
                <i class="fa-solid fa-moon" id="themeIcon"></i>
            </button>

            <a href="staff_profile.php" class="profile-section">
                <div style="text-align: right; margin-right: 10px;">
                    <p style="font-size: 12px; font-weight: 800;"><?php echo $staffDisplayName; ?></p>
                    <p style="font-size: 9px; color: var(--text-muted);">STAFF</p>
                </div>
                <img src="https://ui-avatars.com/api/?name=<?php echo $staffDisplayName; ?>&background=4f46e5&color=fff" class="profile-img">
            </a>
        </div>
    </header>

    <aside class="sidebar" id="sidebar">
        <nav>
            <a href="staff_dashboard.php" class="active"><i class="fa-solid fa-house"></i> <span class="link-text">Dashboard</span></a>
            <a href="staff_studymaterial.php"><i class="fa-solid fa-file-lines"></i> <span class="link-text">Materials</span></a>
            <a href="staff_questionbank.php"><i class="fa-solid fa-book"></i> <span class="link-text">Q. Bank</span></a>
             <a href="staff_syllabus.php"><i class="fa-solid fa-book"></i> <span class="link-text"> Syllabus</span></a>
            <a href="staff_student_queries.php"><i class="fa-solid fa-message"></i> <span class="link-text">Queries</span></a>
        </nav>
        <div style="margin-top: auto; padding-top: 100px;">
            <a href="staff_logout.php" style="color: #ef4444;"><i class="fa-solid fa-power-off"></i> <span class="link-text">Logout</span></a>
        </div>
    </aside>

    <main class="main-content" id="mainContent">
        <div style="margin-bottom: 30px;">
            <h1 id="greeting">Welcome</h1>
            <p style="color: var(--text-muted);">Real-time monitoring of ZEALHUB resources.</p>
        </div>

        <!-- STATS GRID (MIDDLE BUTTONS CLICKABLE) -->
        <div class="stats-grid">
            <a href="staff_student_queries.php" class="stat-card">
                <div class="icon-box" style="background: #eef2ff; color: #4f46e5;"><i class="fa-solid fa-comments"></i></div>
                <div><p style="font-size: 11px; color: var(--text-muted);">QUERIES</p><h2 style="font-size: 24px;"><?php echo $total_queries; ?></h2></div>
            </a>
            <a href="staff_studymaterial.php" class="stat-card">
                <div class="icon-box" style="background: #fff7ed; color: #f97316;"><i class="fa-solid fa-file-pdf"></i></div>
                <div><p style="font-size: 11px; color: var(--text-muted);">MATERIALS</p><h2 style="font-size: 24px;"><?php echo $total_sm; ?></h2></div>
            </a>
            <a href="staff_questionbank.php" class="stat-card">
                <div class="icon-box" style="background: #f0fdf4; color: #22c55e;"><i class="fa-solid fa-vault"></i></div>
                <div><p style="font-size: 11px; color: var(--text-muted);">Q. BANK</p><h2 style="font-size: 24px;"><?php echo $total_qb; ?></h2></div>
            </a>
            <div class="stat-card">
                <div class="icon-box" style="background: #fdf2f8; color: #ec4899;"><i class="fa-solid fa-users"></i></div>
                <div><p style="font-size: 11px; color: var(--text-muted);">STUDENTS</p><h2 style="font-size: 24px;"><?php echo $total_students; ?></h2></div>
            </div>
        </div>

        <div class="info-grid">
            <div class="content-card">
                <h3>System Information</h3>
                <p style="margin-top: 15px; font-size: 14px;">The dashboard is connected to the live ZEALHUB server. Any changes to study materials or question banks will be updated instantly on the student mobile app.</p>
                <div style="margin-top: 20px; display: flex; gap: 15px;">
                    <div style="flex:1; padding: 15px; background: var(--bg); border-radius: 12px; border-left: 4px solid var(--primary);">
                        <p style="font-weight: 700;">Database Sync</p>
                        <p style="font-size: 12px; color: var(--text-muted);">Status: Active</p>
                    </div>
                    <div style="flex:1; padding: 15px; background: var(--bg); border-radius: 12px; border-left: 4px solid #10b981;">
                        <p style="font-weight: 700;">Response Speed</p>
                        <p style="font-size: 12px; color: var(--text-muted);">Average: 0.4s</p>
                    </div>
                </div>
            </div>

            <!-- QUICK ACCESS -->
            <div class="content-card">
                <h3>Quick Tasks</h3>
                <div style="margin-top: 15px;">
                    <a href="staff_student_queries.php" class="task-item"><i class="fa-solid fa-reply"></i> Reply to Queries</a>
                    <a href="staff_studymaterial.php" class="task-item"><i class="fa-solid fa-upload"></i> Upload Notes</a>
                    <a href="staff_questionbank.php" class="task-item"><i class="fa-solid fa-plus"></i> New QB Entry</a>
                </div>
            </div>
        </div>

        <section class="about-us">
            <h2>About ZEALHUB</h2>
            <p>ZEALHUB is the official academic portal of <strong>Zeal College of Engineering and Research, Pune</strong>. We empower faculty to deliver high-quality education through digital accessibility.</p>
        </section>
    </main>

    <script>
        // 1. Sidebar Toggle Logic
        const toggleBtn = document.getElementById('toggleBtn');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });

        // 2. Theme Toggle Logic (Light/Dark Mode)
        const themeBtn = document.getElementById('themeBtn');
        const themeIcon = document.getElementById('themeIcon');
        const currentTheme = localStorage.getItem('theme') || 'light';

        // Set initial theme on load
        document.body.setAttribute('data-theme', currentTheme);
        updateIcon(currentTheme);

        themeBtn.addEventListener('click', () => {
            let theme = document.body.getAttribute('data-theme');
            let newTheme = (theme === 'light') ? 'dark' : 'light';
            
            document.body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon(newTheme);
        });

        function updateIcon(theme) {
            if(theme === 'dark') {
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            } else {
                themeIcon.classList.replace('fa-sun', 'fa-moon');
            }
        }

        // 3. Greeting Logic
        const hour = new Date().getHours();
        const staff = "<?php echo $staffDisplayName; ?>";
        let greet = (hour < 12) ? "Good Morning, " + staff : (hour < 17) ? "Good Afternoon, " + staff : "Good Evening, " + staff;
        document.getElementById('greeting').innerText = greet + "!";
    </script>
</body>
</html>
