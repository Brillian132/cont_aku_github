<?php
// Koneksi ke database
$servername = "localhost";
$username = "username_db";
$password = "password_db";
$dbname = "nama_database";

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil data dari POST
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$city = $_POST['city'];
$province = $_POST['province'];
$postal_code = $_POST['postal_code'];
$shipping_method = $_POST['shipping_method'];
$payment_method = $_POST['payment_method'];
$total_amount = $_POST['total_amount'];

// Siapkan query SQL
$sql = "INSERT INTO orders (customer_name, email, phone, address, city, province, postal_code, shipping_method, payment_method, total_amount, order_date)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

// Gunakan prepared statement untuk mencegah SQL injection
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssd", $name, $email, $phone, $address, $city, $province, $postal_code, $shipping_method, $payment_method, $total_amount);

// Eksekusi query
if ($stmt->execute()) {
    $order_id = $stmt->insert_id;
    
    // Simpan item pesanan ke order_items
    $items = json_decode($_POST['order_items'], true);
    
    foreach ($items as $item) {
        $product_id = $item['product_id'];
        $product_name = $item['product'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        
        $item_sql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price) 
                     VALUES (?, ?, ?, ?, ?)";
        $item_stmt = $conn->prepare($item_sql);
        $item_stmt->bind_param("iisid", $order_id, $product_id, $product_name, $quantity, $price);
        $item_stmt->execute();
        $item_stmt->close();
    }
    
    echo json_encode(["status" => "success", "order_id" => $order_id]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

$stmt->close();
$conn->close();
?>
