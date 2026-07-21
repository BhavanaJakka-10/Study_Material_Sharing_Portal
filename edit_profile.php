<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include("db.php"); 

if(!isset($_SESSION['student'])) {
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['student'];
$message = "";

// 1. FETCH CURRENT DATA
$query = "
    SELECT s.*, sp.*, sa.*, sp.mobile AS student_mobile 
    FROM student s
    LEFT JOIN student_profile sp ON s.id = sp.student_id
    LEFT JOIN student_academic sa ON s.id = sa.student_id
    WHERE s.id = '$student_id'
";
$result = mysqli_query($conn, $query);
$student = mysqli_fetch_assoc($result);

// 2. UPDATE LOGIC
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize Inputs
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $father_name = mysqli_real_escape_string($conn, $_POST['father_name']);
    $father_mobile = mysqli_real_escape_string($conn, $_POST['father_mobile']);
    $mother_name = mysqli_real_escape_string($conn, $_POST['mother_name']);
    $mother_mobile = mysqli_real_escape_string($conn, $_POST['mother_mobile']);
    $dept = mysqli_real_escape_string($conn, $_POST['department']);
    $sem = mysqli_real_escape_string($conn, $_POST['semester']);
    $blood_group = mysqli_real_escape_string($conn, $_POST['blood_group']);

    $errors = [];
    
    // Validation
    if (!preg_match('/^[0-9]{10}$/', $mobile)) $errors[] = "Mobile number must be 10 digits.";
    if (strtotime($dob) > time()) $errors[] = "Date of Birth cannot be in the future.";

    if (empty($errors)) {
        // --- FILE UPLOAD HANDLING ---
        $file_updates = "";
        
        // Define mapping: Form Name => Folder Name
        $file_map = [
            'photo' => 'photos',
            'aadhaar_file' => 'aadhaar',
            'pan_file' => 'pan',
            'ssc_file' => 'ssc',
            'hsc_file' => 'hsc',
            'lc_file' => 'lc'
        ];

        foreach ($file_map as $field => $folder) {
            if (!empty($_FILES[$field]['name'])) {
                $target_dir = "assets/uploads/$folder/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                
                $file_ext = pathinfo($_FILES[$field]["name"], PATHINFO_EXTENSION);
                $new_filename = $field . "_" . $student_id . "_" . time() . "." . $file_ext;
                $target_file = $target_dir . $new_filename;

                if (move_uploaded_file($_FILES[$field]["tmp_name"], $target_file)) {
                    $file_updates .= ", $field = '$new_filename'";
                }
            }
        }

        // Update Tables
        $sql1 = "UPDATE student SET name = '$name' WHERE id = '$student_id'";
        
        $sql2 = "UPDATE student_profile SET 
                    mobile = '$mobile', dob = '$dob', gender = '$gender', 
                    address = '$address', city = '$city', state = '$state', 
                    pincode = '$pincode', father_name = '$father_name', 
                    father_mobile = '$father_mobile', mother_name = '$mother_name', 
                    mother_mobile = '$mother_mobile', blood_group = '$blood_group' 
                    $file_updates
                  WHERE student_id = '$student_id'";

        $sql3 = "UPDATE student_academic SET department = '$dept', semester = '$sem' WHERE student_id = '$student_id'";

        if (mysqli_query($conn, $sql1) && mysqli_query($conn, $sql2) && mysqli_query($conn, $sql3)) {
            $message = "<div class='alert success'>Profile and Documents updated successfully! <a href='profile.php'>View Profile</a></div>";
            header("Refresh:2");
        } else {
            $message = "<div class='alert danger'>Update Failed: " . mysqli_error($conn) . "</div>";
        }
    } else {
        $message = "<div class='alert danger'>" . implode("<br>", $errors) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | ZealHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root { --primary: #4361ee; --sidebar-bg: #0f172a; --bg-light: #f8fafc; --text-main: #1e293b; --white: #ffffff; --border: #e2e8f0; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-light); display: flex; color: var(--text-main); min-height: 100vh; }

        .sidebar { width: 280px; background: var(--sidebar-bg); color: white; padding: 40px 20px; position: fixed; height: 100vh; display: flex; flex-direction: column; align-items: center; }
        .profile-img-preview { width: 110px; height: 110px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.1); object-fit: cover; margin-bottom: 15px; }

        .content { margin-left: 280px; flex: 1; padding: 40px; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .form-card { background: var(--white); border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .form-card h2 { font-size: 18px; color: var(--primary); margin-bottom: 25px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; display: flex; align-items: center; gap: 10px; }

        .grid-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 15px; }
        .form-group label { font-size: 14px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea { padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; outline: none; }
        
        .file-input-group { border: 1px dashed #cbd5e1; padding: 15px; border-radius: 8px; background: #fcfcfc; }
        .status-badge { font-size: 11px; padding: 2px 8px; border-radius: 10px; font-weight: 600; margin-top: 5px; display: inline-block; }
        .status-exists { background: #dcfce7; color: #166534; }
        .status-none { background: #f1f5f9; color: #64748b; }

        .btn-save { background: var(--primary); color: white; border: none; padding: 15px 40px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-save:hover { background: #374dc7; }
        
        .nav-menu { width: 100%; list-style: none; margin-top: 20px;}
        .nav-menu a { display: flex; align-items: center; gap: 12px; color: #94a3b8; text-decoration: none; padding: 12px 15px; border-radius: 8px; }
        .nav-menu .active a { background: var(--primary); color: white; }
    </style>
</head>
<body>

<aside class="sidebar">
    <img src="<?= !empty($student['photo']) ? 'assets/uploads/photos/'.$student['photo'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png' ?>" class="profile-img-preview" id="preview">
    <h2><?= htmlspecialchars($student['name']) ?></h2>
    <ul class="nav-menu" style="margin-top: 30px;">
        <li><a href="student_dashboard.php"><i class="fa fa-house"></i> Dashboard</a></li>
        <li class="active"><a href="profile.php"><i class="fa fa-user"></i> Profile</a></li>
        <li><a href="student_logout.php" style="color:#f87171"><i class="fa fa-power-off"></i> Logout</a></li>
    </ul>
</aside>

<main class="content">
    <div class="header-flex">
        <h1>Edit Profile</h1>
        <a href="profile.php" style="text-decoration:none; color: var(--text-muted); font-weight:500;"><i class="fa fa-arrow-left"></i> Back</a>
    </div>

    <?= $message ?>

    <form action="" method="POST" enctype="multipart/form-data">
        
        <!-- 1. Basic Info -->
        <div class="form-card">
            <h2><i class="fa fa-user"></i> Basic Information</h2>
            <div class="grid-form">
                <div class="form-group">
                    <label>Profile Photo</label>
                    <input type="file" name="photo" accept="image/*" onchange="previewImage(event)">
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= $student['name'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Mobile (10 Digits)</label>
                    <input type="number" name="mobile" value="<?= $student['student_mobile'] ?>" maxlength="10" oninput="if (this.value.length > 10) this.value = this.value.slice(0, 10);" required>
                </div>
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" value="<?= $student['dob'] ?>" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Blood Group</label>
                    <select name="blood_group">
                        <?php foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg): ?>
                            <option value="<?= $bg ?>" <?= ($student['blood_group'] == $bg) ? 'selected' : '' ?>><?= $bg ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. Academic -->
        <div class="form-card">
            <h2><i class="fa fa-graduation-cap"></i> Academic Details</h2>
            <div class="grid-form">
                <div class="form-group">
                    <label>Department</label>
                    <select name="department">
                        <?php 
                        $depts = ['Information Technology', 'Computer Engineering', 'AIDS', 'Civil Engineering', 'Mechanical Engineering', 'Electrical Engineering', 'ENTC', 'Chemical Engineering'];
                        foreach($depts as $d): ?>
                            <option value="<?= $d ?>" <?= ($student['department'] == $d) ? 'selected' : '' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <select name="semester">
                        <?php for($i=1; $i<=8; $i++) echo "<option value='$i' ".($student['semester']==$i?'selected':'').">Semester $i</option>"; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- 3. Documents Section (NEW) -->
        <div class="form-card">
            <h2><i class="fa fa-file-upload"></i> Student Documents</h2>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 20px;">Upload PDF or Images only. Leaving field empty will keep current file.</p>
            <div class="grid-form">
                <?php 
                $docs = [
                    'aadhaar_file' => 'Aadhaar Card',
                    'pan_file' => 'PAN Card',
                    'ssc_file' => 'SSC (10th) Marksheet',
                    'hsc_file' => 'HSC (12th) Marksheet',
                    'lc_file' => 'Leaving Certificate (LC)'
                ];
                foreach($docs as $key => $label): ?>
                <div class="form-group file-input-group">
                    <label><?= $label ?></label>
                    <input type="file" name="<?= $key ?>">
                    <?php if(!empty($student[$key])): ?>
                        <span class="status-badge status-exists"><i class="fa fa-check"></i> Already Uploaded</span>
                    <?php else: ?>
                        <span class="status-badge status-none">Not Uploaded</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 4. Address -->
        <div class="form-card">
            <h2><i class="fa fa-map-marker-alt"></i> Address</h2>
            <div class="form-group"><label>Full Address</label><textarea name="address" rows="2"><?= $student['address'] ?></textarea></div>
            <div class="grid-form">
                <div class="form-group">
                    <label>State</label>
                    <select name="state">
                        <?php foreach(['Maharashtra', 'Gujarat', 'Karnataka', 'Goa', 'Delhi'] as $st): ?>
                            <option value="<?= $st ?>" <?= ($student['state'] == $st) ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>City</label>
                    <select name="city">
                        <?php foreach(['Pune', 'Mumbai', 'Nagpur', 'Nashik', 'Aurangabad'] as $ct): ?>
                            <option value="<?= $ct ?>" <?= ($student['city'] == $ct) ? 'selected' : '' ?>><?= $ct ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Pincode</label>
                    <input type="number" name="pincode" value="<?= $student['pincode'] ?>" oninput="if(this.value.length>6) this.value=this.value.slice(0,6);">
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-bottom: 100px;">
            <button type="submit" class="btn-save"><i class="fa fa-save"></i> Save All Changes</button>
        </div>
    </form>
</main>

<script>
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){ document.getElementById('preview').src = reader.result; }
        reader.readAsDataURL(event.target.files[0]);
    }
</script>

</body>
</html>