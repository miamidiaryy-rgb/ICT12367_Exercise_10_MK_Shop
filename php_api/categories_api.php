<?php
require_once "condb.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $conn->prepare("SELECT category_id, category_name FROM categories ORDER BY category_id ASC");
    $stmt->execute();
    echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit();
}

$input = json_decode(file_get_contents("php://input"), true) ?? [];
$action = $input['action'] ?? '';

try {
    if ($method === 'POST' && ($action === '' || $action === 'create')) {
        $name = trim($input['category_name'] ?? '');
        if ($name === '') {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "กรุณากรอกชื่อประเภทสินค้า"]);
            exit();
        }
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (:name)");
        $stmt->execute([':name' => $name]);
        http_response_code(201);
        echo json_encode(["success" => true, "message" => "เพิ่มประเภทสินค้าเรียบร้อยแล้ว", "category_id" => $conn->lastInsertId()]);
    } elseif ($method === 'PUT' || ($method === 'POST' && $action === 'update')) {
        $id = intval($input['category_id'] ?? 0);
        $name = trim($input['category_name'] ?? '');
        if ($id <= 0 || $name === '') {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ข้อมูลไม่ครบถ้วน"]);
            exit();
        }
        $stmt = $conn->prepare("UPDATE categories SET category_name = :name WHERE category_id = :id");
        $stmt->execute([':name' => $name, ':id' => $id]);
        echo json_encode(["success" => true, "message" => "แก้ไขประเภทสินค้าเรียบร้อยแล้ว"]);
    } elseif ($method === 'DELETE' || ($method === 'POST' && $action === 'delete')) {
        $id = intval($input['category_id'] ?? ($_GET['category_id'] ?? 0));
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ไม่พบรหัสประเภทสินค้า"]);
            exit();
        }
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(["success" => true, "message" => "ลบประเภทสินค้าเรียบร้อยแล้ว"]);
    }
} catch (PDOException $e) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
