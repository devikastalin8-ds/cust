<?php
session_start();
include '../db.php';

$message = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name           = trim($_POST['name']);
    $email          = trim($_POST['email']);
    $password       = trim($_POST['password']);
    $phone          = trim($_POST['phone']);
    $id_proof_type  = trim($_POST['id_proof_type']);
    $id_proof_number= trim($_POST['id_proof_number']);
    $id_proof_file  = "";

    // Handle File Upload for ID Proof Document
    if (isset($_FILES['id_proof_file']) && $_FILES['id_proof_file']['error'] == 0) {
        $target_dir = "../uploads/id_proofs/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_ext = strtolower(pathinfo($_FILES["id_proof_file"]["name"], PATHINFO_EXTENSION));
        $new_filename = time() . "_" . rand(1000, 9999) . "." . $file_ext;
        $target_file = $target_dir . $new_filename;

        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
            if (move_uploaded_file($_FILES["id_proof_file"]["tmp_name"], $target_file)) {
                $id_proof_file = $new_filename;
            }
        }
    }

    $check = $conn->query("SELECT * FROM owners WHERE email='$email'");
    if ($check->num_rows > 0) {
        $message = "Email is already registered! Please log in.";
        $msg_type = "danger";
    } else {
        $sql = "INSERT INTO owners (name, email, password, phone, id_proof_type, id_proof_number, id_proof_file, is_verified) 
                VALUES ('$name', '$email', '$password', '$phone', '$id_proof_type', '$id_proof_number', '$id_proof_file', 0)";
        if ($conn->query($sql) === TRUE) {
            $message = "Owner account created successfully! Your ID proof has been submitted to the Admin for verification.";
            $msg_type = "success";
        } else {
            $message = "Error: " . $conn->error;
            $msg_type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Registration | Auditorium Booking</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <div class="nav-container">
            <div class="logo"><a href="../index.php">🎭 Auditorium Booking</a></div>
            <nav>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="login.php">Owner Login</a></li>
                    <li><a href="register.php" class="active">Register as Owner</a></li>
                    <li><a href="../customer/login.php">Customer Portal</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container" style="max-width: 600px;">
            <h2 style="text-align:center; margin-bottom: 6px; color:#1e3a8a;">Auditorium Owner Registration</h2>
            <p style="text-align:center; color:#64748b; font-size:14px; margin-bottom: 22px;">Submit your details & ID proof for genuine owner verification</p>
            
            <?php if ($message != ""): ?>
                <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label>Full Name / Business Name:</label>
                    <input type="text" name="name" required placeholder="Enter owner or organization name">
                </div>

                <div class="form-group">
                    <label>Email Address:</label>
                    <input type="email" name="email" required placeholder="owner@domain.com">
                </div>

                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required placeholder="Create password">
                </div>

                <div class="form-group">
                    <label>Contact Phone Number:</label>
                    <input type="text" name="phone" required placeholder="10-digit mobile number">
                </div>

                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 18px; margin-bottom: 20px;">
                    <h4 style="color:#0f172a; margin-bottom:12px; font-size:15px; display:flex; align-items:center; gap:6px;">
                        🛡️ Genuine Owner Verification Proof
                    </h4>

                    <div class="form-group">
                        <label>Government ID / Business License Type:</label>
                        <select name="id_proof_type" required>
                            <option value="Aadhaar Card">🪪 Aadhaar Card</option>
                            <option value="PAN Card">🪪 PAN Card</option>
                            <option value="Business License">📜 Business / Trade License</option>
                            <option value="Property Ownership Proof">🏠 Property Ownership Tax Deed</option>
                            <option value="GSTIN Registration">📄 GSTIN Certificate</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>ID Proof / License Number:</label>
                        <input type="text" name="id_proof_number" required placeholder="e.g. 1234-5678-9012 or LIC-884920">
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label>Upload ID Proof Document (JPG, PNG, PDF):</label>
                        <input type="file" name="id_proof_file" accept=".jpg,.jpeg,.png,.pdf" required style="padding:6px;">
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-block" style="padding:12px; font-size:15px;">Register & Submit Verification</button>
            </form>

            <p style="text-align: center; margin-top: 20px; font-size: 14px;">
                Already registered? <a href="login.php" style="color: #16a34a; font-weight:600;">Owner Login</a>
            </p>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System</p>
    </footer>

</body>
</html>