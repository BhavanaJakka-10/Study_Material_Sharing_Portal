<?php
session_start();
include("db.php");

// Security Check
if(!isset($_SESSION['staff'])){
    header("Location: staff_login.php");
    exit();
}

$staffID = $_SESSION['staff'];
$staffName = isset($_SESSION['staff_name']) ? $_SESSION['staff_name'] : $staffID;

$message = "";
$msg_type = "";

// Handle File Upload
if(isset($_POST['upload'])){
    // Logic for "Other" subject
    $subject = ($_POST['subject'] == 'Other') ? mysqli_real_escape_string($conn, $_POST['custom_subject']) : mysqli_real_escape_string($conn, $_POST['subject']);
    
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $filename = $_FILES['file']['name'];
    $temp = $_FILES['file']['tmp_name'];
    $target_dir = "uploads/";
    
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
    $newname = time()."_" . preg_replace("/[^a-zA-Z0-9]/", "_", $title) . "." . $file_ext;

    if(move_uploaded_file($temp, $target_dir . $newname)){
        $conn->query("INSERT INTO question_bank (subject, year, title, description, file_name) 
                      VALUES ('$subject', '$year', '$title', '$description', '$newname')");
        
        // Log Activity
        $log_msg = "Uploaded Question Bank: $title ($subject - $year)";
        $conn->query("INSERT INTO portal_activity (user_name, user_role, action_type, message) VALUES ('$staffName', 'Staff', 'Upload', '$log_msg')");
        
        $message = "Document uploaded to bank successfully!";
        $msg_type = "success";
    } else {
        $message = "Upload failed. Check folder permissions.";
        $msg_type = "error";
    }
}

// Handle File Deletion
if(isset($_GET['delete'])){
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $res = $conn->query("SELECT title, file_name FROM question_bank WHERE id=$id");
    if($row = $res->fetch_assoc()){
        if(file_exists("uploads/".$row['file_name'])) unlink("uploads/".$row['file_name']);
        
        $title = $row['title'];
        $conn->query("DELETE FROM question_bank WHERE id=$id");
        
        // Log Activity
        $log_msg = "Deleted Question Bank item: $title";
        $conn->query("INSERT INTO portal_activity (user_name, user_role, action_type, message) VALUES ('$staffName', 'Staff', 'Delete', '$log_msg')");
        
        header("Location: staff_questionbank.php");
        exit();
    }
}

$materials_query = $conn->query("SELECT * FROM question_bank ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Bank | ZEALHUB</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4f46e5;
            --bg: #f3f4f9;
            --header-bg: #ffffff;
            --sidebar-bg: #ffffff;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --input-bg: #f8fafc;
            --glow: rgba(79, 70, 229, 0.4);
        }

        [data-theme="dark"] {
            --bg: #0f172a;
            --header-bg: #0f172a;
            --sidebar-bg: #1e293b;
            --card-bg: #1e293b;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border: #334155;
            --input-bg: #0f172a;
            --glow: rgba(99, 102, 241, 0.6);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; transition: background 0.3s, color 0.3s; }
        body { background: var(--bg); color: var(--text-main); overflow: hidden; }

        /* --- HEADER --- */
        .header {
            height: 75px; background: var(--header-bg); border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center; padding: 0 25px;
            position: fixed; top: 0; width: 100%; z-index: 1000;
        }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .menu-btn { background: var(--primary); color: white; border: none; width: 40px; height: 40px; border-radius: 10px; cursor: pointer; font-size: 18px; }
        .logo { font-size: 22px; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: 8px; text-decoration: none; }

        .header-right { display: flex; align-items: center; gap: 15px; }
        .theme-btn { background: var(--primary); color: white; border: none; width: 40px; height: 40px; border-radius: 12px; cursor: pointer; }
        .profile-pill { display: flex; align-items: center; gap: 12px; padding: 6px 15px; border-radius: 12px; border: 1px solid var(--border); background: var(--card-bg); text-decoration: none; color: inherit; }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 80px; height: 100vh; background: var(--sidebar-bg); border-right: 1px solid var(--border);
            position: fixed; top: 75px; left: 0; transition: 0.4s; padding-top: 20px; display: flex; flex-direction: column; align-items: center; z-index: 999;
        }
        .sidebar.expanded { width: 260px; align-items: flex-start; padding-left: 20px; }
        .sidebar a { color: var(--text-muted); text-decoration: none; display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; margin-bottom: 15px; border-radius: 12px; }
        .sidebar.expanded a { width: 90%; justify-content: flex-start; padding-left: 15px; }
        .sidebar a span { display: none; font-size: 16px; font-weight: 600; margin-left: 15px; }
        .sidebar.expanded a span { display: inline; }
        
        .sidebar a:hover, .sidebar a.active { background: var(--primary); color: white !important; box-shadow: 0 0 15px var(--glow); transform: scale(1.05); }

        /* --- MAIN CONTENT --- */
        .main-content { margin-left: 80px; margin-top: 75px; padding: 40px; height: calc(100vh - 75px); overflow-y: auto; transition: 0.4s; }
        .main-content.pushed { margin-left: 260px; }

        .container { max-width: 1000px; margin: 0 auto; }
        .card { background: var(--card-bg); border-radius: 24px; padding: 35px; border: 1px solid var(--border); margin-bottom: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }

        /* --- FORM --- */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: var(--text-muted); }
        .form-control { width: 100%; padding: 14px; border-radius: 12px; border: 1px solid var(--border); background: var(--input-bg); color: var(--text-main); outline: none; }
        #custom_subject_div { display: none; margin-top: 10px; }

        .btn-upload { background: var(--primary); color: white; padding: 15px; border: none; border-radius: 12px; font-weight: 700; width: 100%; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-upload:hover { box-shadow: 0 0 15px var(--glow); transform: translateY(-2px); }

        /* --- TABLE --- */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: var(--text-muted); font-size: 12px; border-bottom: 2px solid var(--border); text-transform: uppercase; }
        td { padding: 18px 15px; border-bottom: 1px solid var(--border); font-size: 14px; }
        .file-box { width: 40px; height: 40px; background: #eef2ff; color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .badge-year { background: #fef3c7; color: #d97706; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }

        .msg { padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; }
        .success { background: #dcfce7; color: #15803d; }
        .error { background: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body data-theme="light">

    <header class="header">
        <div class="header-left">
            <button class="menu-btn" id="menuBtn"><i class="fa-solid fa-bars"></i></button>
            <a href="staff_dashboard.php" class="logo"><i class="fa-solid fa-bolt"></i> ZEALHUB</a>
        </div>
        
        <div class="header-right">
            <button class="theme-btn" id="themeBtn"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
            <a href="staff_profile.php" class="profile-pill">
                <div style="text-align: right;">
                    <p style="font-size: 12px; font-weight: 800;"><?php echo htmlspecialchars($staffName); ?></p>
                    <p style="font-size: 9px; color: var(--text-muted);">STAFF</p>
                </div>
                <div style="width: 32px; height: 32px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">AS</div>
            </a>
        </div>
    </header>

    <aside class="sidebar" id="sidebar">
        <a href="staff_dashboard.php"><i class="fa-solid fa-house"></i> <span>Dashboard</span></a>
        <a href="staff_studymaterial.php"><i class="fa-solid fa-file-invoice"></i> <span>Materials</span></a>
        <a href="staff_questionbank.php" class="active"><i class="fa-solid fa-book"></i> <span>Q. Bank</span></a>
        <a href="student_queries.php"><i class="fa-solid fa-message"></i> <span>Queries</span></a>
        <a href="staff_logout.php" style="margin-top: auto; color: #ef4444; margin-bottom: 30px;"><i class="fa-solid fa-power-off"></i> <span>Logout</span></a>
    </aside>

    <main class="main-content" id="mainContent">
        <div class="container">
            <h1 style="font-size: 28px; font-weight: 800; margin-bottom: 5px;">Question Bank Portal</h1>
            <p style="color: var(--text-muted); margin-bottom: 30px;">Manage university papers and question sets.</p>

            <?php if($message) echo "<div class='msg $msg_type'>$message</div>"; ?>

            <div class="card">
                <h3 style="margin-bottom: 20px;">Add New Document</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Subject</label>
                            <select name="subject" id="subject_select" class="form-control" required onchange="checkOther(this)">
                                <option value="">-- Select Subject --</option>
                                <option value="Data Structures">Data Structures</option>
                                <option value="Web Development">Web Development</option>
                                <option value="Other">Other (Specify)</option>
                            </select>
                            <div id="custom_subject_div">
                                <input type="text" name="custom_subject" id="custom_subject" class="form-control" placeholder="Enter subject name">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Academic Year</label>
                            <input type="text" name="year" class="form-control" placeholder="e.g. 2023-24" required>
                        </div>
                        <div class="form-group">
                            <label>Document Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Mid-Term Exam" required>
                        </div>
                        <div class="form-group">
                            <label>Choose File</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Briefly describe the file..."></textarea>
                    </div>
                    <button type="submit" name="upload" class="btn-upload"><i class="fa-solid fa-cloud-arrow-up"></i> Upload to Bank</button>
                </form>
            </div>

            <div class="card">
                <h3 style="margin-bottom: 20px;">Recently Uploaded Papers</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Year</th>
                                <th>Title</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($materials_query->num_rows > 0): while($row = $materials_query->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $row['subject']; ?></strong></td>
                                <td><span class="badge-year"><?php echo $row['year']; ?></span></td>
                                <td>
                                    <div style="font-weight:700;"><?php echo $row['title']; ?></div>
                                    <div style="font-size:11px; color:var(--text-muted);"><?php echo $row['description']; ?></div>
                                </td>
                                <td>
                                    <a href="uploads/<?php echo $row['file_name']; ?>" target="_blank" style="color:var(--primary); font-weight:bold; text-decoration:none;">View</a>
                                    <a href="?delete=<?php echo $row['id']; ?>" style="color:#ef4444; font-weight:bold; text-decoration:none; margin-left:15px;" onclick="return confirm('Permanently delete?')">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="4" style="text-align:center; padding:30px;">No records found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        function checkOther(el) {
            const div = document.getElementById('custom_subject_div');
            const input = document.getElementById('custom_subject');
            if(el.value === 'Other') {
                div.style.display = 'block';
                input.required = true;
            } else {
                div.style.display = 'none';
                input.required = false;
            }
        }

        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('expanded');
            mainContent.classList.toggle('pushed');
        });

        const themeBtn = document.getElementById('themeBtn');
        const themeIcon = document.getElementById('themeIcon');
        themeBtn.addEventListener('click', () => {
            let body = document.body;
            let theme = body.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            body.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            themeIcon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        });

        window.onload = () => {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.body.setAttribute('data-theme', savedTheme);
            themeIcon.className = savedTheme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        };
    </script>
</body>
</html>
