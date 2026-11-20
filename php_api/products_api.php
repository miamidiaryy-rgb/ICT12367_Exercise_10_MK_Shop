<?php
require_once "condb.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
    if ($category_id > 0) {
        $stmt = $conn->prepare(
            "SELECT p.product_id, p.product_name, p.category_id, c.category_name, p.price, p.stock, p.image
             FROM products p
             INNER JOIN categories c ON p.category_id = c.category_id
             WHERE p.category_id = :cid
             ORDER BY p.product_id DESC"
        );
        $stmt->execute([':cid' => $category_id]);
    } else {
        $stmt = $conn->prepare(
            "SELECT p.product_id, p.product_name, p.category_id, c.category_name, p.price, p.stock, p.image
             FROM products p
             INNER JOIN categories c ON p.category_id = c.category_id
             ORDER BY p.product_id DESC"
        );
        $stmt->execute();
    }
    echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit();
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents("php://input"), true) ?? [];
} else {
    $input = $_POST;
}

$action = $input['action'] ?? '';
$image_name = trim($input['image'] ?? '');

if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . "/uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $image_name = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['image_file']['name']));
        move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $image_name);
    }
}

try {
    if ($method === 'POST' && ($action === '' || $action === 'create')) {
        $stmt = $conn->prepare(
            "INSERT INTO products (product_name, category_id, price, stock, image)
             VALUES (:name, :cid, :price, :stock, :img)"
        );
        $stmt->execute([
            ':name' => trim($input['product_name'] ?? ''),
            ':cid' => intval($input['category_id'] ?? 0),
            ':price' => floatval($input['price'] ?? 0),
            ':stock' => intval($input['stock'] ?? 0),
            ':img' => $image_name,
        ]);
        http_response_code(201);
        echo json_encode(["success" => true, "message" => "เพิ่มสินค้าเรียบร้อยแล้ว", "product_id" => $conn->lastInsertId()]);
    } elseif ($method === 'PUT' || ($method === 'POST' && $action === 'update')) {
        $stmt = $conn->prepare(
            "UPDATE products
             SET product_name = :name, category_id = :cid, price = :price, stock = :stock, image = :img
             WHERE product_id = :id"
        );
        $stmt->execute([
            ':name' => trim($input['product_name'] ?? ''),
            ':cid' => intval($input['category_id'] ?? 0),
            ':price' => floatval($input['price'] ?? 0),
            ':stock' => intval($input['stock'] ?? 0),
            ':img' => $image_name,
            ':id' => intval($input['product_id'] ?? 0),
        ]);
        echo json_encode(["success" => true, "message" => "แก้ไขสินค้าเรียบร้อยแล้ว"]);
    } elseif ($method === 'DELETE' || ($method === 'POST' && $action === 'delete')) {
        $id = intval($input['product_id'] ?? ($_GET['product_id'] ?? 0));
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(["success" => true, "message" => "ลบสินค้าเรียบร้อยแล้ว"]);
    }
} catch (PDOException $e) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
