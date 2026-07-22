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

$message = "";
$msg_type = "";

// --- Handle Deletion Logic ---
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $file_query = $conn->query("SELECT file_path FROM study_materials WHERE id = '$delete_id'");
    if ($file_query->num_rows > 0) {
        $file_data = $file_query->fetch_assoc();
        $file_to_delete = $file_data['file_path'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
        $sql_delete = "DELETE FROM study_materials WHERE id = '$delete_id'";
        if ($conn->query($sql_delete)) {
            $message = "Material deleted successfully!";
            $msg_type = "success";
        }
    }
}

// --- Handle File Upload Logic ---
if (isset($_POST['upload_btn'])) {
    $subject = ($_POST['subject'] == 'Other') ? mysqli_real_escape_string($conn, $_POST['custom_subject']) : mysqli_real_escape_string($conn, $_POST['subject']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    
    $file_name = $_FILES['material_file']['name'];
    $file_tmp = $_FILES['material_file']['tmp_name'];
    $target_dir = "uploads/materials/";
    
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $new_file_name = time() . "_" . preg_replace("/[^a-zA-Z0-9]/", "_", $title) . "." . $file_ext;
    $target_file = $target_dir . $new_file_name;

    if (move_uploaded_file($file_tmp, $target_file)) {
        $sql = "INSERT INTO study_materials (subject, title, file_path, uploaded_by, upload_date) 
                VALUES ('$subject', '$title', '$target_file', '$staffID', NOW())";
        if ($conn->query($sql)) {
            $message = "Material published successfully!";
            $msg_type = "success";
        }
    } else {
        $message = "Upload failed. Check folder permissions.";
        $msg_type = "error";
    }
}

$materials_query = $conn->query("SELECT * FROM study_materials ORDER BY upload_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Materials | ZEALHUB</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { 
            --primary: #4f46e5; --bg: #f3f4f9; --header-bg: #ffffff; 
            --sidebar-bg: #ffffff; --card-bg: #ffffff; --text-main: #1e293b; 
            --text-muted: #64748b; --border: #e2e8f0; --input-bg: #f8fafc;
            --glow: rgba(79, 70, 229, 0.4); 
        }
        [data-theme="dark"] { 
            --bg: #0f172a; --header-bg: #0f172a; --sidebar-bg: #1e293b; 
            --card-bg: #1e293b; --text-main: #f1f5f9; --text-muted: #94a3b8; 
            --border: #334155; --input-bg: #0f172a; --glow: rgba(99, 102, 241, 0.6);
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
        .menu-btn { background: var(--primary); color: white; border: none; width: 40px; height: 40px; border-radius: 10px; cursor: pointer; font-size: 18px; }
        .logo { font-size: 22px; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: 8px; text-decoration: none; }

        .header-right { display: flex; align-items: center; gap: 15px; }
        .theme-btn { background: var(--primary); color: white; border: none; width: 40px; height: 40px; border-radius: 12px; cursor: pointer; }
        .profile-pill { display: flex; align-items: center; gap: 12px; padding: 6px 15px; border-radius: 12px; border: 1px solid var(--border); background: var(--card-bg); text-decoration: none; color: inherit; }


        /* Sidebar with Lighting Focus */
        .sidebar { width: 80px; height: 100vh; background: var(--sidebar-bg); border-right: 1px solid var(--border); position: fixed; top: 75px; left: 0; transition: 0.4s; padding-top: 20px; display: flex; flex-direction: column; align-items: center; z-index: 999; }
        .sidebar.expanded { width: 260px; align-items: flex-start; padding-left: 15px; }
        .sidebar a { color: var(--text-muted); text-decoration: none; display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; margin-bottom: 15px; border-radius: 12px; }
        .sidebar.expanded a { width: 90%; justify-content: flex-start; padding-left: 15px; gap: 15px; }
        .sidebar a i { font-size: 22px; min-width: 24px; text-align: center; }
        .sidebar a span { display: none; font-size: 16px; font-weight: 600; }
        .sidebar.expanded a span { display: inline; }

        /* Lighting Hover Effect */
        .sidebar a:hover, .sidebar a.active {
            background: var(--primary) !important;
            color: #fff !important;
            box-shadow: 0 0 15px var(--glow);
            transform: scale(1.08);
        }

        /* Content Area */
        .main-content { margin-left: 80px; margin-top: 75px; padding: 40px; height: calc(100vh - 75px); overflow-y: auto; transition: 0.4s; }
        .main-content.pushed { margin-left: 260px; }
        .card { background: var(--card-bg); border-radius: 24px; padding: 30px; border: 1px solid var(--border); margin-bottom: 30px; }
        .form-control { width: 100%; padding: 14px; border-radius: 12px; border: 1px solid var(--border); background: var(--input-bg); color: var(--text-main); outline: none; }
        .btn-upload { background: var(--primary); color: white; padding: 15px; border: none; border-radius: 12px; font-weight: 700; width: 100%; cursor: pointer; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: var(--text-muted); border-bottom: 2px solid var(--border); font-size: 12px; text-transform: uppercase; }
        td { padding: 18px 15px; border-bottom: 1px solid var(--border); font-size: 14px; }
        .btn-delete { color: #ef4444; font-weight: bold; text-decoration: none; margin-left: 15px; }
    </style>
</head>
<body data-theme="light">

    <header class="header">
    <div class="header-left">
            <button class="menu-btn" id="menuBtn"><i class="fa-solid fa-bars"></i></button>
            <a href="staff_dashboard.php" class="logo"><i class="fa-solid fa-bolt"></i> ZEALHUB</a>
        </div>
        <div class="header-right" style="display: flex; align-items:center; gap:15px;">
            <button class="theme-btn" id="themeBtn"><i class="fa-solid fa-moon"></i></button>
            <div style="display:flex; align-items:center; gap:10px; padding:5px 15px; border:1px solid var(--border); border-radius:12px; background:var(--card-bg);">
                <div style="text-align: right;">
                    <p style="font-size: 12px; font-weight: 800;"><?php echo htmlspecialchars($staffDisplayName); ?></p>
                    <p style="font-size: 9px; color: var(--text-muted);">STAFF</p>
                </div>
                <div style="width:32px; height:32px; background:var(--primary); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:12px;">AS</div>
            </div>
        </div>
    </header>

    <aside class="sidebar" id="sidebar">
        <a href="staff_dashboard.php"><i class="fa-solid fa-house"></i> <span>Dashboard</span></a>
        <a href="staff_studymaterial.php" class="active"><i class="fa-solid fa-file-invoice"></i> <span>Materials</span></a>
        <a href="staff_questionbank.php"><i class="fa-solid fa-book"></i> <span>Q. Bank</span></a>
        <a href="student_queries.php"><i class="fa-solid fa-message"></i> <span>Queries</span></a>
        <a href="staff_logout.php" style="margin-top: auto; color: #ef4444; margin-bottom: 30px;"><i class="fa-solid fa-power-off"></i> <span>Logout</span></a>
    </aside>

    <main class="main-content" id="mainContent">
        <div style="max-width: 1000px; margin: 0 auto;">
            <h1 style="font-size: 28px; font-weight: 800; margin-bottom: 5px;">Study Material Portal</h1>
            <p style="color: var(--text-muted); margin-bottom: 30px;">Manage your documents with lighting focus effects.</p>

            <?php if($message != ""): ?>
                <div style="padding:15px; border-radius:12px; margin-bottom:20px; background:#dcfce7; color:#15803d; font-weight:600; border: 1px solid #bcf0da;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label style="display:block; margin-bottom:8px; font-weight:600; font-size:14px; color:var(--text-muted);">Category</label>
                            <select name="subject" class="form-control" required onchange="const d=document.getElementById('custom_subject_div'); d.style.display=(this.value==='Other')?'block':'none';">
                                <option value="">-- Select --</option>
                                <option value="Data Structures">Data Structures</option>
                                <option value="Web Development">Web Development</option>
                                <option value="Other">Other</option>
                            </select>
                            <div id="custom_subject_div" style="display:none; margin-top:10px;"><input type="text" name="custom_subject" class="form-control" placeholder="Subject Name"></div>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:8px; font-weight:600; font-size:14px; color:var(--text-muted);">Document Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                    </div>
                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600; font-size:14px; color:var(--text-muted);">PDF Attachment</label>
                        <input type="file" name="material_file" class="form-control" accept=".pdf" required>
                    </div>
                    <button type="submit" name="upload_btn" class="btn-upload">Publish Material</button>
                </form>
            </div>

            <div class="card">
                <table>
                    <thead><tr><th>Resource</th><th>Subject</th><th>Date</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php while($row = $materials_query->fetch_assoc()): ?>
                        <tr>
                            <td><div style="display:flex; align-items:center; gap:12px;"><div style="width:35px; height:35px; background:#eef2ff; color:var(--primary); border-radius:8px; display:flex; align-items:center; justify-content:center;"><i class="fa-solid fa-file-pdf"></i></div><b><?php echo $row['title']; ?></b></div></td>
                            <td><?php echo $row['subject']; ?></td>
                            <td><?php echo date('d M, Y', strtotime($row['upload_date'])); ?></td>
                            <td>
                                <a href="<?php echo $row['file_path']; ?>" target="_blank" style="color:var(--primary); font-weight:bold; text-decoration:none;">View</a>
                                <a href="?delete_id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Permanently delete?')">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('expanded');
            mainContent.classList.toggle('pushed');
        });

        const themeBtn = document.getElementById('themeBtn');
        themeBtn.addEventListener('click', () => {
            let body = document.body;
            let theme = body.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            body.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            themeBtn.innerHTML = theme === 'dark' ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
        });

        window.onload = () => {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.body.setAttribute('data-theme', savedTheme);
            const icon = savedTheme === 'dark' ? 'fa-sun' : 'fa-moon';
            themeBtn.innerHTML = `<i class="fa-solid fa-${icon}"></i>`;
        };
    </script>
</body>
</html>
