<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT * FROM users WHERE id='$id'");
$user = mysqli_fetch_assoc($query);

$message = '';

if (isset($_POST['update'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET fullname='$fullname', password='$password' WHERE id='$id'");
    } else {
        mysqli_query($conn, "UPDATE users SET fullname='$fullname' WHERE id='$id'");
    }
    
    $_SESSION['fullname'] = $fullname;
    $message = '<div class="alert alert-success">Profile updated successfully!</div>';
    
    // Refresh user data
    $query = mysqli_query($conn, "SELECT * FROM users WHERE id='$id'");
    $user = mysqli_fetch_assoc($query);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile - BlackMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a1a; font-family: 'Inter', sans-serif; }
        .edit-box { background: #2a2a2a; padding: 2rem; border-radius: 10px; border: 1px solid #c0392b; }
        .edit-box label { color: #aaa; font-size: 0.85rem; }
        .edit-box input { background: #1a1a1a; border: 1px solid #444; color: #fff; }
        .edit-box input:focus { border-color: #c0392b; box-shadow: 0 0 0 0.2rem rgba(192,57,43,0.25); }
        .btn-red { background: #c0392b; color: #fff; }
        .btn-red:hover { background: #96281b; color: #fff; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Black<span style="color:#c0392b;">Market</span></a>
            <a href="user.php" class="btn btn-sm btn-outline-light">← Back to Profile</a>
        </div>
    </nav>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="edit-box">
                    <h3 class="text-white mb-3">✏️ Edit Profile</h3>
                    
                    <?php echo $message; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="fullname" class="form-control" value="<?php echo $user['fullname']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo $user['email']; ?>" disabled>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter new password">
                        </div>
                        <button type="submit" name="update" class="btn btn-red w-100">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>