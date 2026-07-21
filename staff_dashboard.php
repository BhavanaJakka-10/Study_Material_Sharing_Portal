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

// Logic for Profile Image
$profilePic = "uploads/default.png"; 
if (file_exists("uploads/" . $staffID . ".jpg")) {
    $profilePic = "uploads/" . $staffID . ".jpg";
}

// Placeholder Stats (In a real app, fetch these from DB)
// $query_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM queries WHERE status='pending'");
// $total_queries = mysqli_fetch_assoc($query_count)['total'];
$total_queries = 5; 
$total_materials = 24;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard | Study Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --sidebar-bg: #0f172a;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-body: #f8fafc;
            --glass: rgba(255, 255, 255, 0.7);
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            background-color: var(--bg-body);
            background-image: radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.08) 0px, transparent 50%),
                              radial-gradient(at 100% 100%, rgba(99, 102, 241, 0.05) 0px, transparent 50%);
            min-height: 100vh;
            color: var(--text-main);
            overflow: hidden; /* Prevent double scrollbars */
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade { animation: fadeIn 0.6s ease-out forwards; }

        /*================ HEADER =================*/
        .header {
            height: 70px; width: 100%; display: flex; justify-content: space-between;
            align-items: center; padding: 0 30px; background: var(--glass);
            backdrop-filter: blur(15px); border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            position: fixed; top: 0; z-index: 1000;
        }

        .logo { font-size: 20px; font-weight: 800; color: var(--primary); letter-spacing: -1px; display: flex; align-items: center; gap: 10px; }
        
        .header-right { display: flex; align-items: center; gap: 20px; }
        
        #live-clock {
            font-weight: 600; color: var(--text-muted); font-size: 14px;
            padding: 8px 15px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0;
        }

        .profile-pill {
            display: flex; align-items: center; gap: 12px;
            background: #fff; padding: 5px 5px 5px 15px;
            border-radius: 50px; border: 1px solid #e2e8f0;
            transition: 0.3s; cursor: pointer; text-decoration: none;
        }
        .profile-pill:hover { border-color: var(--primary); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1); }

        .staff-info { display: flex; flex-direction: column; text-align: right; }
        .staff-display-name { font-size: 13px; font-weight: 700; color: var(--text-main); }
        .staff-role { font-size: 10px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; }

        .avatar-wrapper { position: relative; width: 35px; height: 35px; }
        .avatar-wrapper img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary); }

        /*================ LAYOUT =================*/
        .wrapper { display: flex; height: 100vh; padding-top: 70px; }

        .sidebar {
            width: 260px; background: var(--sidebar-bg); margin: 20px;
            border-radius: 20px; display: flex; flex-direction: column;
            padding: 25px 15px; transition: 0.3s;
        }

        .sidebar ul { list-style: none; flex-grow: 1; }
        .sidebar a {
            display: flex; align-items: center; gap: 12px; text-decoration: none;
            color: #94a3b8; padding: 12px 15px; border-radius: 12px;
            font-size: 14px; transition: 0.3s; margin-bottom: 5px;
        }
        .sidebar a:hover, .sidebar a.active { background: rgba(255, 255, 255, 0.08); color: #fff; }
        .sidebar a.active { background: var(--primary); color: white; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3); }

        /*================ CONTENT =================*/
        .content-area { 
            flex: 1; overflow-y: auto; padding: 25px 25px 25px 5px; 
            scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent;
        }

        .welcome-section {
            background: white; padding: 35px; border-radius: 24px;
            margin-bottom: 25px; border: 1px solid #e2e8f0;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: var(--card-shadow);
        }

        .welcome-text h2 { font-size: 28px; font-weight: 800; color: #0f172a; }
        .welcome-text p { color: var(--text-muted); margin-top: 5px; }

        /* Stats Grid */
        .stats-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px;
        }
        .stat-box {
            background: #fff; padding: 20px; border-radius: 20px; border: 1px solid #e2e8f0;
            display: flex; align-items: center; gap: 15px;
        }
        .stat-icon {
            width: 50px; height: 50px; border-radius: 14px; display: flex;
            align-items: center; justify-content: center; font-size: 20px;
        }

        /* Action Grid */
        .action-grid {
            display: grid; grid-template-columns: 2fr 1fr; gap: 25px;
        }

        .dashboard-card {
            background: #fff; border-radius: 24px; padding: 25px;
            border: 1px solid #e2e8f0; box-shadow: var(--card-shadow);
        }

        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card-header h3 { font-size: 18px; font-weight: 700; }

        .quick-actions-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;
        }
        
        .action-btn {
            text-decoration: none; padding: 20px; border-radius: 18px;
            background: #f8fafc; border: 1px solid #e2e8f0; text-align: center;
            transition: 0.3s;
        }
        .action-btn:hover { border-color: var(--primary); background: #fff; transform: translateY(-3px); }
        .action-btn i { font-size: 24px; color: var(--primary); margin-bottom: 10px; display: block; }
        .action-btn span { font-size: 14px; font-weight: 600; color: var(--text-main); }

        .activity-item {
            display: flex; gap: 15px; padding: 15px 0; border-bottom: 1px solid #f1f5f9;
        }
        .activity-item:last-child { border: none; }
        .activity-point { width: 10px; height: 10px; border-radius: 50%; background: var(--primary); margin-top: 5px; }
        .activity-info p { font-size: 13px; font-weight: 500; }
        .activity-info span { font-size: 11px; color: var(--text-muted); }

        .btn-logout {
            margin-top: auto; display: flex; align-items: center; gap: 10px;
            color: #f87171; padding: 12px 15px; text-decoration: none;
            font-weight: 600; font-size: 14px; border-radius: 12px; transition: 0.3s;
        }
        .btn-logout:hover { background: rgba(248, 113, 113, 0.1); }

    </style>
</head>
<body>

    <header class="header">
        <div class="logo">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>ZEALHUB</span>
        </div>

        <div class="header-right">
            <div id="live-clock">00:00:00 PM</div>
            
                <div class="staff-info">
                    <span class="staff-display-name"><?php echo htmlspecialchars($staffDisplayName); ?></span>
    
                </div>
            
        </div>
    </header>

    <div class="wrapper">
        <nav class="sidebar">
            <ul>
                <li><a href="staff_dashboard.php" class="active"><i class="fa-solid fa-house-chimney"></i> Dashboard</a></li>
                <li><a href="staff_studymaterial.php"><i class="fa-solid fa-cloud-arrow-up"></i> Study Materials</a></li>
                <li><a href="staff_questionbank.php"><i class="fa-solid fa-book"></i> Question Bank</a></li>
                <li><a href="student_queries.php"><i class="fa-solid fa-circle-question"></i> Student Queries</a></li>
                <li><a href="Staff_alert.php"><i class="fa-solid fa-bell"></i> Announcements</a></li>
                <li><a href="staff_profile.php"><i class="fa-solid fa-user-gear"></i> Account Settings</a></li>
            </ul>
            
            <a href="staff_logout.php" class="btn-logout">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout Session
            </a>
        </nav>

        <main class="content-area">
            <!-- Welcome Hero -->
            <div class="welcome-section animate-fade">
                <div class="welcome-text">
                    <h2 id="greeting">Welcome back!</h2>
                    <p>You have <strong><?php echo $total_queries; ?></strong> pending queries to answer today.</p>
                </div>
                <img src="https://cdn-icons-png.flaticon.com/512/6024/6024190.png" alt="Staff Illustration" style="height: 100px;">
            </div>

            <!-- Stats Overview -->
            <div class="stats-grid animate-fade" style="animation-delay: 0.1s;">
                <div class="stat-box">
                    <div class="stat-icon" style="background: #e0e7ff; color: #4338ca;"><i class="fa-solid fa-file-lines"></i></div>
                    <div><h4 style="font-size: 20px;"><?php echo $total_materials; ?></h4><p style="font-size: 12px; color: var(--text-muted);">Materials</p></div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon" style="background: #fef3c7; color: #b45309;"><i class="fa-solid fa-message"></i></div>
                    <div><h4 style="font-size: 20px;"><?php echo $total_queries; ?></h4><p style="font-size: 12px; color: var(--text-muted);">New Queries</p></div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon" style="background: #dcfce7; color: #15803d;"><i class="fa-solid fa-users"></i></div>
                    <div><h4 style="font-size: 20px;">142</h4><p style="font-size: 12px; color: var(--text-muted);">Active Students</p></div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon" style="background: #fae8ff; color: #a21caf;"><i class="fa-solid fa-chart-line"></i></div>
                    <div><h4 style="font-size: 20px;">98%</h4><p style="font-size: 12px; color: var(--text-muted);">Uptime</p></div>
                </div>
            </div>

            <div class="action-grid">
                <!-- Quick Actions -->
                <div class="dashboard-card animate-fade" style="animation-delay: 0.2s;">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="quick-actions-grid">
                        <a href="staff_studymaterial.php" class="action-btn">
                            <i class="fa-solid fa-plus"></i>
                            <span>Upload Notes</span>
                        </a>
                        <a href="staff_questionbank.php" class="action-btn">
                            <i class="fa-solid fa-folder-plus"></i>
                            <span>New QB Entry</span>
                        </a>
                        <a href="student_queries.php" class="action-btn">
                            <i class="fa-solid fa-reply-all"></i>
                            <span>Reply Queries</span>
                        </a>
                        <a href="Staff_alert.php" class="action-btn">
                            <i class="fa-solid fa-bullhorn"></i>
                            <span>Send Alert</span>
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="dashboard-card animate-fade" style="animation-delay: 0.3s;">
                    <div class="card-header">
                        <h3>Recent Activity</h3>
                    </div>
                    <div class="activity-feed">
                        <div class="activity-item">
                            <div class="activity-point"></div>
                            <div class="activity-info">
                                <p>Uploaded "Unit 3 - Data Structures"</p>
                                <span>2 hours ago</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-point" style="background: #f59e0b;"></div>
                            <div class="activity-info">
                                <p>Replied to Rahul's Query</p>
                                <span>5 hours ago</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-point" style="background: #10b981;"></div>
                            <div class="activity-info">
                                <p>Profile updated successfully</p>
                                <span>Yesterday</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Real-time Clock function
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; 
            const timeString = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
            document.getElementById('live-clock').textContent = timeString;
        }

        // Dynamic Greeting function
        function setGreeting() {
            const hour = new Date().getHours();
            const staffName = "<?php echo htmlspecialchars($staffDisplayName); ?>";
            let greeting = "";

            if (hour < 12) greeting = "Good Morning, " + staffName + "! ☀️";
            else if (hour < 17) greeting = "Good Afternoon, " + staffName + "! 🌤️";
            else greeting = "Good Evening, " + staffName + "! 🌙";

            document.getElementById('greeting').textContent = greeting;
        }

        // Initialize
        setInterval(updateClock, 1000);
        updateClock();
        setGreeting();

        // Add some hover interaction to cards
        const cards = document.querySelectorAll('.stat-box, .action-btn');
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.borderColor = '#6366f1';
            });
            card.addEventListener('mouseleave', () => {
                card.style.borderColor = '#e2e8f0';
            });
        });
    </script>
</body>
</html>