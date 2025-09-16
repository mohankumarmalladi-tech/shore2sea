<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "shore2door";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle different requests
switch ($method) {
    case 'GET':
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            
            if ($action == 'products') {
                // Get all products or filtered products
                $category = isset($_GET['category']) ? $_GET['category'] : '';
                $search = isset($_GET['search']) ? $_GET['search'] : '';
                
                $sql = "SELECT * FROM products";
                $conditions = [];
                
                if (!empty($category) && $category != 'all') {
                    $conditions[] = "category = '$category'";
                }
                
                if (!empty($search)) {
                    $conditions[] = "name LIKE '%$search%'";
                }
                
                if (!empty($conditions)) {
                    $sql .= " WHERE " . implode(' AND ', $conditions);
                }
                
                $result = $conn->query($sql);
                $products = [];
                
                while ($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }
                
                echo json_encode($products);
            }
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (isset($data['action'])) {
            $action = $data['action'];
            
            if ($action == 'register') {
                // User registration
                $name = $data['name'];
                $email = $data['email'];
                $mobile = $data['mobile'];
                $password = password_hash($data['password'], PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO users (name, email, mobile, password) VALUES ('$name', '$email', '$mobile', '$password')";
                
                if ($conn->query($sql) {
                    echo json_encode(["success" => true, "message" => "User registered successfully"]);
                } else {
                    echo json_encode(["success" => false, "message" => "Registration failed"]);
                }
            }
            
            if ($action == 'login') {
                // User login
                $email = $data['email'];
                $password = $data['password'];
                
                $sql = "SELECT * FROM users WHERE email = '$email'";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    
                    if (password_verify($password, $user['password'])) {
                        echo json_encode(["success" => true, "message" => "Login successful", "user" => $user]);
                    } else {
                        echo json_encode(["success" => false, "message" => "Invalid password"]);
                    }
                } else {
                    echo json_encode(["success" => false, "message" => "User not found"]);
                }
            }
            
            if ($action == 'add_to_cart') {
                // Add product to cart
                $user_id = $data['user_id'];
                $product_id = $data['product_id'];
                $quantity = $data['quantity'];
                
                // Check if product already in cart
                $checkSql = "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id";
                $checkResult = $conn->query($checkSql);
                
                if ($checkResult->num_rows > 0) {
                    $sql = "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = $user_id AND product_id = $product_id";
                } else {
                    $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)";
                }
                
                if ($conn->query($sql)) {
                    echo json_encode(["success" => true, "message" => "Product added to cart"]);
                } else {
                    echo json_encode(["success" => false, "message" => "Failed to add product to cart"]);
                }
            }
            
            if ($action == 'place_order') {
                // Place order
                $user_id = $data['user_id'];
                $items = $data['items'];
                $total = $data['total'];
                $payment_method = $data['payment_method'];
                
                // Create order
                $sql = "INSERT INTO orders (user_id, total_amount, payment_method) VALUES ($user_id, $total, '$payment_method')";
                
                if ($conn->query($sql)) {
                    $order_id = $conn->insert_id;
                    
                    // Add order items
                    foreach ($items as $item) {
                        $product_id = $item['product_id'];
                        $quantity = $item['quantity'];
                        $price = $item['price'];
                        
                        $itemSql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, $product_id, $quantity, $price)";
                        $conn->query($itemSql);
                    }
                    
                    // Clear user's cart
                    $clearCartSql = "DELETE FROM cart WHERE user_id = $user_id";
                    $conn->query($clearCartSql);
                    
                    echo json_encode(["success" => true, "message" => "Order placed successfully", "order_id" => $order_id]);
                } else {
                    echo json_encode(["success" => false, "message" => "Failed to place order"]);
                }
            }
        }
        break;
        
    case 'DELETE':
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            
            if ($action == 'remove_from_cart') {
                $user_id = $_GET['user_id'];
                $product_id = $_GET['product_id'];
                
                $sql = "DELETE FROM cart WHERE user_id = $user_id AND product_id = $product_id";
                
                if ($conn->query($sql)) {
                    echo json_encode(["success" => true, "message" => "Product removed from cart"]);
                } else {
                    echo json_encode(["success" => false, "message" => "Failed to remove product from cart"]);
                }
            }
        }
        break;
}

$conn->close();
?>