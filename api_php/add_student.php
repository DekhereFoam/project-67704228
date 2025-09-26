<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// เชื่อมต่อฐานข้อมูล
include 'condb.php';

try {
    // ตรวจสอบคำขอที่ได้รับจาก Client ตามประเภทของคำ ว่าเป็น GET หรือ POST
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == 'POST') {
        // รับข้อมูลจาก Client
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        
        // Debug: แสดงข้อมูลที่ได้รับ
        error_log("Received data: " . print_r($data, true));
        
        // ตรวจสอบว่า JSON decode สำเร็จหรือไม่
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "success" => false, 
                "message" => "Invalid JSON format: " . json_last_error_msg()
            ]);
            exit;
        }
        
        // ตรวจสอบว่าได้รับข้อมูลหรือไม่
        if ($data === null || !is_array($data)) {
            echo json_encode([
                "success" => false, 
                "message" => "No data received or invalid data format"
            ]);
            exit;
        }

        // ตรวจสอบค่าที่จำเป็น - ตรวจสอบทั้ง isset และ empty
        $required_fields = ['first_name', 'last_name', 'phone', 'email'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            echo json_encode([
                "success" => false, 
                "message" => "Missing or empty required fields: " . implode(', ', $missing_fields),
                "received_data" => $data
            ]);
            exit;
        }

        // ทำความสะอาดข้อมูล
        $first_name = trim($data['first_name']);
        $last_name = trim($data['last_name']);
        $phone = trim($data['phone']);
        $email = trim($data['email']);
        
        // ตรวจสอบรูปแบบอีเมล
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "success" => false, 
                "message" => "Invalid email format"
            ]);
            exit;
        }
        
        // ตรวจสอบว่าอีเมลซ้ำหรือไม่
        $checkStmt = $conn->prepare("SELECT student_id FROM students WHERE email = :email");
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            echo json_encode([
                "success" => false, 
                "message" => "อีเมลนี้มีอยู่ในระบบแล้ว"
            ]);
            exit;
        }

        // เพิ่มข้อมูลนักเรียนใหม่ - ใช้โครงสร้าง table ที่มีอยู่
        $stmt = $conn->prepare("INSERT INTO students (first_name, last_name, phone, email) VALUES (:first_name, :last_name, :phone, :email)");
        
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);

        if ($stmt->execute()) {
            $student_id = $conn->lastInsertId();
            echo json_encode([
                "success" => true, 
                "message" => "เพิ่มข้อมูลนักเรียนสำเร็จ",
                "data" => [
                    "student_id" => $student_id,
                    "first_name" => $first_name,
                    "last_name" => $last_name,
                    "phone" => $phone,
                    "email" => $email
                ]
            ]);
        } else {
            $errorInfo = $stmt->errorInfo();
            echo json_encode([
                "success" => false, 
                "message" => "เกิดข้อผิดพลาดในการเพิ่มข้อมูล",
                "error" => $errorInfo[2]
            ]);
        }
    } 
    elseif ($method == 'GET') {
        // ดึงข้อมูลนักเรียนทั้งหมด - ใช้ student_id แทน id
        $stmt = $conn->prepare("SELECT * FROM students ORDER BY student_id DESC");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            "success" => true,
            "message" => "ดึงข้อมูลสำเร็จ",
            "data" => $students
        ]);
    }
    elseif ($method == 'PUT') {
        // แก้ไขข้อมูลนักเรียน
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        
        if (!isset($data['student_id']) || empty($data['student_id'])) {
            echo json_encode([
                "success" => false, 
                "message" => "Student ID is required"
            ]);
            exit;
        }
        
        // ตรวจสอบค่าที่จำเป็น
        $required_fields = ['first_name', 'last_name', 'phone', 'email'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            echo json_encode([
                "success" => false, 
                "message" => "Missing required fields: " . implode(', ', $missing_fields)
            ]);
            exit;
        }
        
        // ตรวจสอบรูปแบบอีเมล
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "success" => false, 
                "message" => "Invalid email format"
            ]);
            exit;
        }
        
        // ตรวจสอบว่าอีเมลซ้ำหรือไม่ (ยกเว้นตัวเอง)
        $checkStmt = $conn->prepare("SELECT student_id FROM students WHERE email = :email AND student_id != :student_id");
        $checkStmt->bindParam(':email', $data['email']);
        $checkStmt->bindParam(':student_id', $data['student_id']);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            echo json_encode([
                "success" => false, 
                "message" => "อีเมลนี้มีอยู่ในระบบแล้ว"
            ]);
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE students SET first_name = :first_name, last_name = :last_name, phone = :phone, email = :email WHERE student_id = :student_id");
        
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':student_id', $data['student_id']);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    "success" => true, 
                    "message" => "แก้ไขข้อมูลนักเรียนสำเร็จ"
                ]);
            } else {
                echo json_encode([
                    "success" => false, 
                    "message" => "ไม่พบข้อมูลที่ต้องการแก้ไข"
                ]);
            }
        } else {
            echo json_encode([
                "success" => false, 
                "message" => "เกิดข้อผิดพลาดในการแก้ไขข้อมูล"
            ]);
        }
    }
    elseif ($method == 'DELETE') {
        // ลบข้อมูลนักเรียน
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        
        if (!isset($data['student_id']) || empty($data['student_id'])) {
            echo json_encode([
                "success" => false, 
                "message" => "Student ID is required"
            ]);
            exit;
        }
        
        $stmt = $conn->prepare("DELETE FROM students WHERE student_id = :student_id");
        $stmt->bindParam(':student_id', $data['student_id']);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    "success" => true, 
                    "message" => "ลบข้อมูลนักเรียนสำเร็จ"
                ]);
            } else {
                echo json_encode([
                    "success" => false, 
                    "message" => "ไม่พบข้อมูลที่ต้องการลบ"
                ]);
            }
        } else {
            echo json_encode([
                "success" => false, 
                "message" => "เกิดข้อผิดพลาดในการลบข้อมูล"
            ]);
        }
    }
    else {
        echo json_encode([
            "success" => false, 
            "message" => "Method not allowed"
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>