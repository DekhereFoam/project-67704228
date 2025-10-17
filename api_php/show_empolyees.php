<?php

include 'condb.php';

$action = $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    // เพิ่ม / แก้ไข / ลบ
    switch($action) {

        case 'add':
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $username = $_POST['username'];
            $password = $_POST['password'];

            // ตรวจสอบ username ซ้ำ
            $checkStmt = $conn->prepare("SELECT id FROM employees WHERE username = :username");
            $checkStmt->bindParam(':username', $username);
            $checkStmt->execute();
            if ($checkStmt->rowCount() > 0) {
                echo json_encode(["error" => "Username นี้มีอยู่ในระบบแล้ว"]);
                exit;
            }

            // เข้ารหัสรหัสผ่าน
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // อัพโหลดไฟล์รูป
            $filename = null;
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
                $targetDir = "uploads/";
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $filename = time() . '_' . basename($_FILES['profile_image']['name']);
                $targetFile = $targetDir . $filename;
                move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile);
            }

            $sql = "INSERT INTO employees (first_name, last_name, username, password, profile_image)
                    VALUES (:first_name, :last_name, :username, :password, :profile_image)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':profile_image', $filename);

            if ($stmt->execute()) {
                echo json_encode(["message" => "เพิ่มพนักงานสำเร็จ"]);
            } else {
                echo json_encode(["error" => "เพิ่มพนักงานล้มเหลว"]);
            }
            break;

        case 'update':
            $id = $_POST['id'];
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $username = $_POST['username'];
            $password = $_POST['password'] ?? '';

            // ตรวจสอบ username ซ้ำ (ยกเว้นตัวเอง)
            $checkStmt = $conn->prepare("SELECT id FROM employees WHERE username = :username AND id != :id");
            $checkStmt->bindParam(':username', $username);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            if ($checkStmt->rowCount() > 0) {
                echo json_encode(["error" => "Username นี้มีอยู่ในระบบแล้ว"]);
                exit;
            }

            // อัพโหลดไฟล์รูป
            $imageSQL = "";
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
                $targetDir = "uploads/";
                $filename = time() . '_' . basename($_FILES['profile_image']['name']);
                $targetFile = $targetDir . $filename;
                move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile);
                $imageSQL = ", profile_image = :profile_image";
            }

            // จัดการ password
            $passwordSQL = "";
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $passwordSQL = ", password = :password";
            }

            $sql = "UPDATE employees SET 
                        first_name = :first_name,
                        last_name = :last_name,
                        username = :username
                        $passwordSQL
                        $imageSQL
                    WHERE id = :id";
            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':id', $id);
            if (!empty($password)) $stmt->bindParam(':password', $hashed_password);
            if (isset($filename)) $stmt->bindParam(':profile_image', $filename);

            if ($stmt->execute()) {
                echo json_encode(["message" => "แก้ไขพนักงานสำเร็จ"]);
            } else {
                echo json_encode(["error" => "แก้ไขพนักงานล้มเหลว"]);
            }
            break;

        case 'delete':
            $id = $_POST['id'];
            
            // ดึงข้อมูลรูปภาพก่อนลบ
            $stmt = $conn->prepare("SELECT profile_image FROM employees WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // ลบข้อมูลจากฐานข้อมูล
            $stmt = $conn->prepare("DELETE FROM employees WHERE id = :id");
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                // ลบไฟล์รูปภาพ (ถ้ามี)
                if ($employee && !empty($employee['profile_image'])) {
                    $file_path = "uploads/" . $employee['profile_image'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
                echo json_encode(["message" => "ลบพนักงานสำเร็จ"]);
            } else {
                echo json_encode(["error" => "ลบพนักงานล้มเหลว"]);
            }
            break;

        default:
            echo json_encode(["error" => "Action ไม่ถูกต้อง"]);
            break;
    }

} else {
    // GET: ดึงข้อมูลพนักงาน (ไม่รวม password)
    $stmt = $conn->prepare("SELECT id, first_name, last_name, username, profile_image, created_at, updated_at FROM employees ORDER BY id DESC");
    if ($stmt->execute()) {
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $employees]);
    } else {
        echo json_encode(["success" => false, "data" => []]);
    }
}
?>