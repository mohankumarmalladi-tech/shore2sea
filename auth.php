<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $action = $data['action'];
    
    if ($action == 'login') {
        $email = $data['email'];
        $password = $data['password'];
        
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                
                echo json_encode(["success" => true, "message" => "Login successful", "user" => $user]);
            } else {
                echo json_encode(["success" => false, "message" => "Invalid password"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "User not found"]);
        }
    }
    
    if ($action == 'register') {
        $name = $data['name'];
        $email = $data['email'];
        $mobile = $data['mobile'];
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Check if user already exists
        $checkSql = "SELECT id FROM users WHERE email = '$email' OR mobile = '$mobile'";
        $checkResult = $conn->query($checkSql);
        
        if ($checkResult->num_rows > 0) {
            echo json_encode(["success" => false, "message" => "User already exists with this email or mobile"]);
        } else {
            $sql = "INSERT INTO users (name, email, mobile, password) VALUES ('$name', '$email', '$mobile', '$password')";
            
            if ($conn->query($sql)) {
                echo json_encode(["success" => true, "message" => "Registration successful"]);
            } else {
                echo json_encode(["success" => false, "message" => "Registration failed"]);
            }
        }
    }
}
?>