<?php
require_once 'db.php';

// Get the term and prevent SQL injection
$term = isset($_GET['term']) ? mysqli_real_escape_string($conn, $_GET['term']) : '';
$suggestions = [];

if (strlen($term) >= 2) {
    // Search for product names that contain the typed term
    // We use DISTINCT to avoid duplicate names in the suggestion list
    $query = "SELECT DISTINCT name FROM products 
              WHERE name LIKE '%$term%' 
              OR brand LIKE '%$term%' 
              LIMIT 6";
              
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $suggestions[] = $row['name'];
        }
    }
}

// Set header to JSON so the browser understands the response
header('Content-Type: application/json');
echo json_encode($suggestions);