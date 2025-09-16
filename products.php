<?php
include 'config.php';

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
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
?>