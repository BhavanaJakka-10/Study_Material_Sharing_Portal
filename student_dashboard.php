<?php
session_start();
include("db.php"); 

if(!isset($_SESSION['student'])) {
    header("Location: student_login.php");
    exit();
}

$id = $_SESSION['student'];

// Fetch Student Name
$query = "SELECT name FROM student WHERE id='$id'";
$result = mysqli_query($conn, $query);
$studentName = ($result && $row = mysqli_fetch_assoc($result)) ? $row['name'] : "Student";

// Fetch Counts
$paperCount = ($res = $conn->query("SELECT COUNT(*) as total FROM question_bank")) ? $res->fetch_assoc()['total'] : 0;
$materialCount = ($res = $conn->query("SELECT COUNT(*) as total FROM study_materials")) ? $res->fetch_assoc()['total'] : 0;

// Fetch Notifications Count (Unread)
$notifCountQuery = "SELECT COUNT(*) as total FROM notifications WHERE (student_id = '$id' OR student_id IS NULL) AND is_read = 0";
$notifCount = ($res = $conn->query($notifCountQuery)) ? $res->fetch_assoc()['total'] : 0;

// Fetch Latest 5 Notifications
$notifications = $conn->query("SELECT * FROM notifications WHERE (student_id = '$id' OR student_id IS NULL) ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | ZEALHUB</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4361ee; --bg: #f3f4f9; --header-bg: #ffffff;
            --sidebar-bg: #ffffff; --card-bg: #ffffff; --text-main: #1e293b;
            --text-muted: #64748b; --border: #e2e8f0; --glow: rgba(67, 97, 238, 0.4);
            --danger: #ef4444;
        }

        [data-theme="dark"] {
            --bg: #0f172a; --header-bg: #0f172a; --sidebar-bg: #1e293b;
            --card-bg: #1e293b; --text-main: #f1f5f9; --text-muted: #94a3b8;
            --border: #334155; --glow: rgba(67, 97, 238, 0.6);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; transition: all 0.3s ease; }
        body { background: var(--bg); color: var(--text-main); overflow: hidden; }

        /* --- HEADER --- */
        .header {
            height: 75px; background: var(--header-bg); border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center; padding: 0 25px;
            position: fixed; top: 0; width: 100%; z-index: 1000;
        }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .menu-btn { background: var(--primary); color: white; border: none; width: 40px; height: 40px; border-radius: 10px; cursor: pointer; }
        .logo { font-size: 22px; font-weight: 800; color: var(--primary); text-decoration: none; display: flex; align-items: center; gap: 8px; }

        .header-right { display: flex; align-items: center; gap: 12px; }
        .icon-btn { 
            background: var(--card-bg); color: var(--text-main); border: 1px solid var(--border); 
            width: 40px; height: 40px; border-radius: 12px; cursor: pointer; position: relative;
            display: flex; align-items: center; justify-content: center;
        }
        .icon-btn:hover { background: var(--primary); color: white; }
        
        /* Notification Badge */
        .badge {
            position: absolute; top: -5px; right: -5px; background: var(--danger);
            color: white; font-size: 10px; padding: 2px 6px; border-radius: 50%;
            border: 2px solid var(--header-bg); font-weight: 800;
        }

        /* Notification Dropdown */
        .notif-dropdown {
            position: absolute; top: 70px; right: 80px; width: 320px; background: var(--card-bg);
            border: 1px solid var(--border); border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            display: none; z-index: 1001; overflow: hidden;
        }
        .notif-dropdown.show { display: block; animation: slideDown 0.3s ease; }
        .notif-header { padding: 15px; border-bottom: 1px solid var(--border); font-weight: 700; display: flex; justify-content: space-between; }
        .notif-item { padding: 12px 15px; border-bottom: 1px solid var(--border); cursor: pointer; text-decoration: none; display: block; color: inherit; }
        .notif-item:hover { background: rgba(67, 97, 238, 0.05); }
        .notif-item p { font-size: 12px; margin-top: 3px; }
        .notif-item small { font-size: 10px; color: var(--text-muted); }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 80px; height: 100vh; background: var(--sidebar-bg); border-right: 1px solid var(--border);
            position: fixed; top: 75px; left: 0; padding-top: 20px; display: flex; flex-direction: column; align-items: center; z-index: 999;
        }
        .sidebar.expanded { width: 260px; align-items: flex-start; padding-left: 20px; }
        .sidebar a { color: var(--text-muted); text-decoration: none; display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; margin-bottom: 15px; border-radius: 12px; }
        .sidebar.expanded a { width: 90%; justify-content: flex-start; padding-left: 15px; }
        .sidebar a span { display: none; font-size: 15px; font-weight: 600; margin-left: 15px; }
        .sidebar.expanded a span { display: inline; }
        .sidebar a:hover, .sidebar a.active { background: var(--primary); color: white !important; box-shadow: 0 0 15px var(--glow); }

        /* --- MAIN CONTENT --- */
        .main-content { margin-left: 80px; margin-top: 75px; padding: 30px; height: calc(100vh - 75px); overflow-y: auto; }
        .main-content.pushed { margin-left: 260px; }

        .stats-grid, .action-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card, .action-btn { background: var(--card-bg); padding: 20px; border-radius: 20px; border: 1px solid var(--border); display: flex; align-items: center; gap: 15px; text-decoration: none; color: inherit; }
        .action-btn { flex-direction: column; text-align: center; padding: 25px; }
        .action-btn:hover { border-color: var(--primary); box-shadow: 0 0 20px var(--glow); transform: translateY(-5px); }
        .icon-box { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; }

        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body data-theme="light">

<header class="header">
    <div class="header-left">
        <button class="menu-btn" id="menuBtn"><i class="fa-solid fa-bars"></i></button>
        <a href="#" class="logo"><i class="fa-solid fa-graduation-cap"></i> ZEALHUB</a>
    </div>
    <div class="header-right">
        <!-- Notification Button -->
        <div style="position: relative;">
            <button class="icon-btn" id="notifBtn">
                <i class="fa-solid fa-bell"></i>
                <?php if($notifCount > 0): ?>
                    <span class="badge"><?= $notifCount ?></span>
                <?php endif; ?>
            </button>
            <!-- Dropdown -->
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header">
                    <span>Notifications</span>
                    <a href="mark_all_read.php" style="font-size: 11px; color: var(--primary); text-decoration: none;">Mark all read</a>
                </div>
                <div style="max-height: 300px; overflow-y: auto;">
                    <?php if($notifications->num_rows > 0): ?>
                        <?php while($n = $notifications->fetch_assoc()): ?>
                            <a href="#" class="notif-item">
                                <strong style="font-size: 13px;"><?= htmlspecialchars($n['title']) ?></strong>
                                <p style="color: var(--text-muted);"><?= htmlspecialchars($n['message']) ?></p>
                                <small><i class="fa-regular fa-clock"></i> <?= date('M d, h:i A', strtotime($n['created_at'])) ?></small>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 13px;">No new notifications</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <button class="icon-btn" id="themeBtn"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
        
        <a href="profile.php" style="display: flex; align-items: center; gap: 12px; padding: 6px 15px; border-radius: 12px; border: 1px solid var(--border); background: var(--card-bg); text-decoration: none; color: inherit;">
            <div style="text-align: right;">
                <p style="font-size: 11px; font-weight: 800;"><?= htmlspecialchars($studentName) ?></p>
                <p style="font-size: 9px; color: var(--text-muted);">STUDENT</p>
            </div>
            <div style="width: 32px; height: 32px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">ST</div>
        </a>
    </div>
</header>

<aside class="sidebar" id="sidebar">
    <a href="student_dashboard.php" class="active"><i class="fa-solid fa-house"></i> <span>Dashboard</span></a>
    <a href="student_questionbank.php"><i class="fa-solid fa-file-pdf"></i> <span>Question Bank</span></a>
    <a href="student_study_material.php"><i class="fa-solid fa-book-open"></i> <span>Materials</span></a>
    <a href="student_syllabus.php"><i class="fa-solid fa-scroll"></i> <span>Syllabus</span></a>
    <a href="student_raise_query.php"><i class="fa-solid fa-clipboard-question"></i> <span>Queries</span></a>
    <a href="student_logout.php" style="margin-top: auto; color: #ef4444; margin-bottom: 30px;"><i class="fa-solid fa-power-off"></i> <span>Logout</span></a>
</aside>

<main class="main-content" id="mainContent">
    <div style="margin-bottom: 30px;">
        <h1 id="greeting">Welcome</h1>
        <p style="color: var(--text-muted);">Your academic command center is ready.</p>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon-box" style="background: rgba(67, 97, 238, 0.1); color: var(--primary);"><i class="fa-solid fa-file-lines"></i></div>
            <div><p style="font-size: 11px; color: var(--text-muted);">PAPERS</p><h2><?= $paperCount ?></h2></div>
        </div>
        <div class="stat-card">
            <div class="icon-box" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="fa-solid fa-book"></i></div>
            <div><p style="font-size: 11px; color: var(--text-muted);">NOTES</p><h2><?= $materialCount ?></h2></div>
        </div>
        <!-- Notification Stat Card -->
        <div class="stat-card" style="cursor:pointer;" onclick="document.getElementById('notifBtn').click()">
            <div class="icon-box" style="background: rgba(239, 68, 68, 0.1); color: var(--danger);"><i class="fa-solid fa-bell"></i></div>
            <div><p style="font-size: 11px; color: var(--text-muted);">ALERTS</p><h2><?= $notifCount ?></h2></div>
        </div>
    </div>

    <h3 style="margin-bottom: 15px;">Quick Access</h3>
    <div class="action-grid">
        <a href="student_questionbank.php" class="action-btn">
            <i class="fa-solid fa-file-circle-question" style="font-size: 30px; color: var(--primary); margin-bottom: 10px;"></i>
            <span>Question Bank</span>
        </a>
        <a href="student_study_material.php" class="action-btn">
            <i class="fa-solid fa-file-pdf" style="font-size: 30px; color: var(--primary); margin-bottom: 10px;"></i>
            <span>Study Material</span>
        </a>
        <a href="student_syllabus.php" class="action-btn">
            <i class="fa-solid fa-list-ul" style="font-size: 30px; color: var(--primary); margin-bottom: 10px;"></i>
            <span>Syllabus</span>
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <div style="background: var(--card-bg); padding: 25px; border-radius: 24px; border: 1px solid var(--border);">
            <h3 style="color: var(--primary); margin-bottom: 15px;">Portal Updates</h3>
            <p>Access all semester-wise resources. New features like query replies and material alerts are now live.</p>
        </div>

        <!-- Dashboard Notification Feed -->
        <div style="background: var(--card-bg); padding: 20px; border-radius: 24px; border: 1px solid var(--border);">
            <h4 style="margin-bottom: 15px;"><i class="fa-solid fa-bullhorn" style="color:var(--primary)"></i> Recent Notifications</h4>
            <?php 
            $notifications->data_seek(0); // Reset pointer
            if($notifications->num_rows > 0): 
                while($n = $notifications->fetch_assoc()): ?>
                <div style="padding: 10px 0; border-bottom: 1px solid var(--border); font-size: 13px;">
                    <strong><?= $n['title'] ?></strong>
                    <p style="font-size: 11px; color: var(--text-muted);"><?= $n['message'] ?></p>
                </div>
            <?php endwhile; else: ?>
                <p style="font-size: 12px; color: var(--text-muted);">No notifications yet.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    // Sidebar logic
    const menuBtn = document.getElementById('menuBtn');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    menuBtn.addEventListener('click', () => {
        sidebar.classList.toggle('expanded');
        mainContent.classList.toggle('pushed');
    });

    // Notification Dropdown toggle
    const notifBtn = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    notifBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        notifDropdown.classList.toggle('show');
    });
    document.addEventListener('click', () => notifDropdown.classList.remove('show'));

    // Theme logic
    const themeBtn = document.getElementById('themeBtn');
    const themeIcon = document.getElementById('themeIcon');
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.body.setAttribute('data-theme', currentTheme);
    themeIcon.className = (currentTheme === 'dark') ? 'fa-solid fa-sun' : 'fa-solid fa-moon';

    themeBtn.addEventListener('click', () => {
        let theme = document.body.getAttribute('data-theme');
        let newTheme = (theme === 'light') ? 'dark' : 'light';
        document.body.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        themeIcon.className = (newTheme === 'dark') ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    });

    // Greeting
    const hour = new Date().getHours();
    const student = "<?= htmlspecialchars($studentName) ?>";
    let greet = (hour < 12) ? "Good Morning" : (hour < 17) ? "Good Afternoon" : "Good Evening";
    document.getElementById('greeting').innerText = `${greet}, ${student}! 👋`;
</script>

</body>
</html>
