<?php
header('Content-Type: application/json');
  // เชื่อมต่อฐานข้อมูล
include 'condb.php';

try {
 //ตรวจสอบคำขอที่ได้รับจาก Client  ตามประเภทของคำ ว่าเป็น GET หรือ POST
    $method = $_SERVER['REQUEST_METHOD'];

   if ($method == 'POST') {
        // รับข้อมูลจาก Client
        $data = json_decode(file_get_contents("php://input"), true);

        // ตรวจสอบค่าที่จำเป็น
        if (isset($data['firstName'], $data['lastName'], $data['phone'], $data['username'], $data['password'])) {
            // เพิ่มข้อมูลลูกค้าใหม่
          $stmt = $conn->prepare("INSERT INTO customers (firstName, lastName, phone, username, password) VALUES (:firstName, :lastName, :phone, :username, :password)");
            $stmt->bindParam(':firstName', $data['firstName']);
            $stmt->bindParam(':lastName', $data['lastName']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':username', $data['username']);
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmt->bindParam(':password', $hashedPassword);

            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Customer added successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Error adding customer"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Missing required fields"]);
        }
    } 
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>