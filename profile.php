<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include("db.php"); 

if(!isset($_SESSION['student'])) {
    die("Session not set. Please <a href='student_login.php'>Login again</a>");
}

$student_id = $_SESSION['student'];

// DEBUG: Uncomment the line below if you keep getting the error to see your ID
// die("Searching for Student ID: " . $student_id); 

// Updated Query: We use 'student' as the base table
$query = "
    SELECT 
        s.name, s.email,
        sp.*, 
        sa.*,
        sp.mobile AS student_mobile 
    FROM student s
    LEFT JOIN student_profile sp ON s.id = sp.student_id
    LEFT JOIN student_academic sa ON s.id = sa.student_id
    WHERE s.id = '$student_id'
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}

$student = mysqli_fetch_assoc($result);

// If the profile is still not found, let's try searching by Name or Email as a backup
if (!$student) {
    // This happens if the session contains a NAME or EMAIL instead of an ID
    $query_backup = "SELECT * FROM student WHERE name = '$student_id' OR email = '$student_id'";
    $result_backup = mysqli_query($conn, $query_backup);
    $student = mysqli_fetch_assoc($result_backup);
    
    if(!$student) {
        die("Error: Profile data not found for ID: " . htmlspecialchars($student_id) . ". <br> Please check if this ID exists in the 'student' table in phpMyAdmin.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Profile | ZealHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --sidebar-bg: #0f172a;
            --bg-light: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --white: #ffffff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-light); display: flex; color: var(--text-main); }

        /* --- Sidebar --- */
        .sidebar {
            width: 280px; background: var(--sidebar-bg); color: white;
            padding: 40px 20px; position: fixed; height: 100vh;
            display: flex; flex-direction: column; align-items: center;
        }
        .profile-img {
            width: 110px; height: 110px; border-radius: 50%;
            border: 3px solid rgba(255,255,255,0.1); object-fit: cover; margin-bottom: 15px;
        }
        .sidebar h2 { font-size: 18px; text-align: center; margin-bottom: 5px; }
        .sidebar p { font-size: 13px; color: #94a3b8; margin-bottom: 30px; }
        .nav-menu { width: 100%; list-style: none; }
        .nav-menu a {
            display: flex; align-items: center; gap: 12px; color: #94a3b8;
            text-decoration: none; padding: 12px 15px; border-radius: 8px; transition: 0.3s;
        }
        .nav-menu a:hover, .nav-menu .active a { background: var(--primary); color: white; }

        /* --- Main Content --- */
        .content { margin-left: 280px; flex: 1; padding: 40px; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .edit-btn { 
            background: var(--primary); color: white; text-decoration: none;
            padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600;
        }

        /* --- Profile Card Styles --- */
        .profile-card {
            background: var(--white); border-radius: 12px; padding: 25px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 25px;
        }
        .profile-card h2 { 
            font-size: 18px; color: var(--primary); margin-bottom: 20px; 
            border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;
            display: flex; align-items: center; gap: 10px;
        }
        
        table { width: 100%; border-collapse: collapse; }
        table th { text-align: left; width: 35%; padding: 12px 0; color: var(--text-main); font-weight: 600; font-size: 14px; }
        table td { padding: 12px 0; color: var(--text-muted); font-size: 14px; }
        table tr { border-bottom: 1px solid #f1f5f9; }
        table tr:last-child { border: none; }

        /* Buttons & Badges */
        .btn-view { background: #e0e7ff; color: var(--primary); padding: 5px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 600; }
        .achievement-list { list-style: none; }
        .achievement-list li { padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .id-card-box { background: #f8fafc; border: 1px dashed #cbd5e1; padding: 20px; border-radius: 12px; margin-top: 20px; }
    </style>
</head>
<body>

<aside class="sidebar">
    <?php if(!empty($student['photo'])): ?>
        <img src="assets/uploads/photos/<?= $student['photo'] ?>" class="profile-img">
    <?php else: ?>
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="profile-img">
    <?php endif; ?>
    
    <h2><?= htmlspecialchars($student['name']) ?></h2>
    <p><?= htmlspecialchars($student['department'] ?? 'Information Technology') ?></p>

    <ul class="nav-menu">
        <li><a href="student_dashboard.php"><i class="fa fa-house"></i> Dashboard</a></li>
        <li class="active"><a href="profile.php"><i class="fa fa-user"></i> Profile</a></li>
        <li><a href="student_logout.php" style="color:#f87171"><i class="fa fa-power-off"></i> Logout</a></li>
    </ul>
</aside>

<main class="content">
    <div class="header-flex">
        <h1>Student Profile</h1>
        <a href="edit_profile.php" class="edit-btn"><i class="fa fa-edit"></i> Edit Profile</a>
    </div>

    <!-- Personal Information -->
    <div class="profile-card">
        <h2><i class="fa fa-info-circle"></i> Personal Information</h2>
        <table>
            <tr><th>Full Name</th><td><?= $student['name'] ?></td></tr>
            <tr><th>Roll Number</th><td><?= $student['roll'] ?></td></tr>
            <tr><th>PRN Number</th><td><?= $student['prn'] ?></td></tr>
            <tr><th>ABC ID</th><td><?= $student['abc_id'] ?></td></tr>
            <tr><th>Department</th><td><?= $student['department'] ?></td></tr>
            <tr><th>Semester</th><td><?= $student['semester'] ?></td></tr>
            <tr><th>Date of Birth</th><td><?= $student['dob'] ?></td></tr>
            <tr><th>Gender</th><td><?= $student['gender'] ?></td></tr>
            <tr><th>Mobile</th><td><?= $student['student_mobile'] ?></td></tr>
            <tr><th>Email</th><td><?= $student['email'] ?></td></tr>
            <tr><th>Aadhaar Number</th><td><?= $student['aadhaar_no'] ?></td></tr>
            <tr><th>Address</th><td><?= $student['address'] ?></td></tr>
            <tr><th>City</th><td> <?= $student['city'] ?></td></tr>
            <tr><th>State</th><td> <?= $student['state'] ?> </td></tr>
            <tr><th>Pincode</th><td> <?= $student['pincode'] ?></td></tr>
        </table>
    </div>

    <!-- Parents Details -->
    <div class="profile-card">
        <h2><i class="fa fa-users"></i> Parents Details</h2>
        <table>
            <tr><th>Father Name</th><td><?= $student['father_name'] ?></td></tr>
            <tr><th>Father Mobile</th><td><?= $student['father_mobile'] ?></td></tr>
            <tr><th>Mother Name</th><td><?= $student['mother_name'] ?></td></tr>
            <tr><th>Mother Mobile</th><td><?= $student['mother_mobile'] ?></td></tr>
        </table>
    </div>

    <!-- Documents Section -->
    <div class="profile-card">
        <h2><i class="fa fa-folder-open"></i> Student Documents</h2>
        <table>
            <?php 
            $docs = [
                'Aadhaar' => 'aadhaar_file', 'PAN Card' => 'pan_file', 
                'SSC' => 'ssc_file', 'HSC' => 'hsc_file', 'LC' => 'lc_file'
            ];
            foreach($docs as $label => $key): ?>
            <tr>
                <th><?= $label ?></th>
                <td>
                    <?php if(!empty($student[$key])): ?>
                        <a href="assets/uploads/<?= str_replace('_file','',$key) ?>/<?= $student[$key] ?>" target="_blank" class="btn-view">View Document</a>
                    <?php else: echo "Not Uploaded"; endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- Fees Section -->
    <div class="profile-card">
        <h2><i class="fa fa-wallet"></i> Fee Details</h2>
        <table>
            <tr>
                <th>Fee Status</th>
                <td>
                    <b style="color: <?= ($student['fees_status'] == 'Paid') ? 'green' : 'red' ?>;">
                        <?= $student['fees_status'] ?>
                    </b>
                </td>
            </tr>
            <tr>
                <th>Receipt</th>
                <td>
                    <?php if(!empty($student['receipt_file'])): ?>
                        <a href="assets/uploads/receipt/<?= $student['receipt_file'] ?>" target="_blank" class="btn-view">Download Receipt</a>
                    <?php else: echo "No Receipt Available"; endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- Medical & Extra -->
    <div class="profile-card">
        <h2><i class="fa fa-heart-pulse"></i> Medical Information</h2>
        <table>
            <tr><th>Blood Group</th><td><?= $student['blood_group'] ?></td></tr>
            <tr><th>Emergency Contact</th><td><?= $student['emergency_contact'] ?></td></tr>
        </table>
    </div>

    <!-- Digital ID Summary -->
    <div class="profile-card">
        <h2><i class="fa fa-id-card"></i> Digital Student ID</h2>
        <div class="id-card-box">
            <div style="display:flex; justify-content: space-between; align-items: center;">
                <div>
                    <p><strong>Name:</strong> <?= $student['name'] ?></p>
                    <p><strong>PRN:</strong> <?= $student['prn'] ?></p>
                    <p><strong>Dept:</strong> <?= $student['department'] ?></p>
                </div>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= $student['prn'] ?>" width="80">
            </div>
            <br>
            <a href="download_id.php" class="edit-btn" style="display:inline-block">Download Digital ID Card</a>
        </div>
    </div>
</main>

</body>
</html>