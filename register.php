<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $fullname = $_POST['fullname'];
    $sex = $_POST['sex'];
    $date_of_birth = $_POST['date_of_birth'];

    $stmt = $conn->prepare("INSERT INTO users (username, password, fullname, sex, date_of_birth) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $password, $fullname, $sex, $date_of_birth);
    
    if ($stmt->execute()) {
        header("Location: login.php?registered=1");
        exit();
    } else {
        $error = "Registration failed: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration</title>
</head>
<body>
    <h1>Register</h1>
    <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
    <?php if (isset($_GET['registered'])) echo "<p style='color:green'>Registration successful! Please login.</p>"; ?>
    
    <form method="post">
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        Full Name: <input type="text" name="fullname" required><br>
        Sex: 
        <select name="sex" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select><br>
        Date of Birth: <input type="date" name="date_of_birth" required><br>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</body>
</html>