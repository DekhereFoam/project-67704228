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
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === "GET") {
        // ดึงข้อมูลนักเรียนทั้งหมด
        $stmt = $conn->prepare("SELECT student_id, first_name, last_name, phone, email, created_at FROM students ORDER BY student_id DESC");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["success" => true, "data" => $result]);
    }
    
    elseif ($method === "POST") {
        // เพิ่มข้อมูลนักเรียนใหม่
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        
        // ตรวจสอบ JSON decode
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "success" => false, 
                "message" => "Invalid JSON format: " . json_last_error_msg()
            ]);
            exit;
        }
        
        // ตรวจสอบข้อมูลที่จำเป็น
        if (!isset($data['first_name']) || !isset($data['last_name']) || 
            !isset($data['phone']) || !isset($data['email']) ||
            empty(trim($data['first_name'])) || empty(trim($data['last_name'])) ||
            empty(trim($data['phone'])) || empty(trim($data['email']))) {
            
            echo json_encode([
                "success" => false, 
                "message" => "กรุณากรอกข้อมูลให้ครบถ้วน (ชื่อ, นามสกุล, เบอร์โทร, อีเมล)"
            ]);
            exit;
        }

        // ตรวจสอบรูปแบบอีเมล
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "success" => false, 
                "message" => "รูปแบบอีเมลไม่ถูกต้อง"
            ]);
            exit;
        }

        // ตรวจสอบอีเมลซ้ำ
        $checkStmt = $conn->prepare("SELECT student_id FROM students WHERE email = :email");
        $checkStmt->bindParam(':email', $data['email']);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            echo json_encode([
                "success" => false, 
                "message" => "อีเมลนี้มีอยู่ในระบบแล้ว"
            ]);
            exit;
        }

        // เพิ่มข้อมูลใหม่
        $stmt = $conn->prepare("INSERT INTO students (first_name, last_name, phone, email) VALUES (:first_name, :last_name, :phone, :email)");
        
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':email', $data['email']);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true, 
                "message" => "เพิ่มข้อมูลนักเรียนสำเร็จ",
                "data" => [
                    "student_id" => $conn->lastInsertId(),
                    "first_name" => $data['first_name'],
                    "last_name" => $data['last_name'],
                    "phone" => $data['phone'],
                    "email" => $data['email']
                ]
            ]);
        } else {
            echo json_encode([
                "success" => false, 
                "message" => "ไม่สามารถเพิ่มข้อมูลได้"
            ]);
        }
    }

    elseif ($method === "DELETE") {
        // ลบข้อมูลนักเรียน
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        // ตรวจสอบ JSON decode
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "success" => false, 
                "message" => "Invalid JSON format"
            ]);
            exit;
        }

        // ตรวจสอบ student_id
        if (!isset($data["student_id"]) || empty($data["student_id"])) {
            echo json_encode([
                "success" => false, 
                "message" => "ไม่พบค่า student_id"
            ]);
            exit;
        }

        $student_id = intval($data["student_id"]);
        
        // ตรวจสอบว่ามีข้อมูลอยู่หรือไม่
        $checkStmt = $conn->prepare("SELECT student_id FROM students WHERE student_id = :student_id");
        $checkStmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() == 0) {
            echo json_encode([
                "success" => false, 
                "message" => "ไม่พบข้อมูลนักเรียนที่ต้องการลบ"
            ]);
            exit;
        }

        // ดำเนินการลบ - แก้ไข table name และ parameter name
        $stmt = $conn->prepare("DELETE FROM students WHERE student_id = :student_id");
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true, 
                "message" => "ลบข้อมูลนักเรียนสำเร็จ"
            ]);
        } else {
            echo json_encode([
                "success" => false, 
                "message" => "ไม่สามารถลบข้อมูลได้"
            ]);
        }
    }

    elseif ($method === "PUT") {
        // แก้ไขข้อมูลนักเรียน
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        // ตรวจสอบข้อมูลที่จำเป็น
        if (!isset($data['student_id']) || !isset($data['first_name']) || 
            !isset($data['last_name']) || !isset($data['phone']) || !isset($data['email']) ||
            empty($data['student_id']) || empty(trim($data['first_name'])) || 
            empty(trim($data['last_name'])) || empty(trim($data['phone'])) || 
            empty(trim($data['email']))) {
            
            echo json_encode([
                "success" => false, 
                "message" => "กรุณากรอกข้อมูลให้ครบถ้วน"
            ]);
            exit;
        }

        // ตรวจสอบรูปแบบอีเมล
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "success" => false, 
                "message" => "รูปแบบอีเมลไม่ถูกต้อง"
            ]);
            exit;
        }

        // ตรวจสอบอีเมลซ้ำ (ยกเว้นตัวเอง)
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

        // แก้ไขข้อมูล
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
                    "message" => "ไม่มีข้อมูลที่ต้องการแก้ไขหรือข้อมูลไม่มีการเปลี่ยนแปลง"
                ]);
            }
        } else {
            echo json_encode([
                "success" => false, 
                "message" => "ไม่สามารถแก้ไขข้อมูลได้"
            ]);
        }
    }

    else {
        echo json_encode([
            "success" => false, 
            "message" => "Method ไม่ถูกต้อง (รองรับเฉพาะ GET, POST, PUT, DELETE)"
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