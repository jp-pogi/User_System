<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['add_hobby'])) {
        $new_hobby = trim($_POST['new_hobby']);
        if (!empty($new_hobby)) {

            $stmt = $conn->prepare("SELECT hobbies FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            
            $hobbies = json_decode($user['hobbies'] ?? '[]', true) ?: [];
            
            
            $hobbies[] = [
                'id' => uniqid(),
                'name' => htmlspecialchars($new_hobby),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            
            $stmt = $conn->prepare("UPDATE users SET hobbies = ? WHERE id = ?");
            $json_hobbies = json_encode($hobbies);
            $stmt->bind_param("si", $json_hobbies, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    
    if (isset($_POST['delete_hobby'])) {
        $hobby_id = $_POST['hobby_id'];
        
        
        $stmt = $conn->prepare("SELECT hobbies FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        
        $hobbies = json_decode($user['hobbies'] ?? '[]', true) ?: [];
        $hobbies = array_filter($hobbies, function($hobby) use ($hobby_id) {
            return $hobby['id'] !== $hobby_id;
        });
        
        
        $stmt = $conn->prepare("UPDATE users SET hobbies = ? WHERE id = ?");
        $json_hobbies = json_encode(array_values($hobbies)); // reindex array
        $stmt->bind_param("si", $json_hobbies, $user_id);
        $stmt->execute();
        $stmt->close();
    }
    
    
    if (isset($_POST['update_address'])) {
        $address = $_POST['address'];
        $stmt = $conn->prepare("UPDATE users SET address = ? WHERE id = ?");
        $stmt->bind_param("si", $address, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}


$stmt = $conn->prepare("SELECT username, fullname, sex, date_of_birth, address, hobbies FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();


$hobbies = json_decode($user['hobbies'] ?? '[]', true) ?: [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <style>
        .hobby-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            margin: 5px 0;
            background: #f5f5f5;
            border-radius: 4px;
        }
        .hobby-actions form {
            display: inline;
            margin-left: 10px;
        }
        .hobby-list {
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($user['username']) ?></h1>
    <p><a href="logout.php">Logout</a></p>
    
    <h2>Your Profile</h2>
    <p><strong>Full Name:</strong> <?= htmlspecialchars($user['fullname']) ?></p>
    <p><strong>Sex:</strong> <?= htmlspecialchars($user['sex']) ?></p>
    <p><strong>Date of Birth:</strong> <?= htmlspecialchars($user['date_of_birth']) ?></p>
    
    <form method="post">
        <h3>Update Address</h3>
        <div>
            <textarea name="address" rows="4" cols="50"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
        </div>
        <button type="submit" name="update_address">Save Address</button>
    </form>
    
    <h3>Your Hobbies</h3>
    <div class="hobby-list">
        <?php if (empty($hobbies)): ?>
            <p>No hobbies added yet.</p>
        <?php else: ?>
            <?php foreach ($hobbies as $hobby): ?>
                <div class="hobby-item">
                    <span><?= htmlspecialchars($hobby['name']) ?></span>
                    <div class="hobby-actions">
                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this hobby?');">
                            <input type="hidden" name="hobby_id" value="<?= $hobby['id'] ?>">
                            <button type="submit" name="delete_hobby">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <h3>Add New Hobby</h3>
    <form method="post">
        <input type="text" name="new_hobby" placeholder="Enter a new hobby" required>
        <button type="submit" name="add_hobby">Add Hobby</button>
    </form>
</body>
</html>