<?php
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $row1 = $conn->query("SELECT COUNT(*) AS CustomerNumber FROM customers")->fetch(PDO::FETCH_ASSOC);
    $row2 = $conn->query("SELECT COUNT(DISTINCT СustomerID) AS CustomerNumber FROM booksprovision WHERE ReturnDate IS NULL OR ReturnDate = '0'")->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Помилка підключення";
    exit;
}
?>
<div class="col-lg-6 statistics customers">
    <h2>Загальна кількість клієнтів в системі</h2>
    <p class="number"><?php echo $row1['CustomerNumber']; ?></p>
</div>
<div class="col-lg-6 statistics customers">
    <h2>Кількість клієнтів, які мають видані книги</h2>
    <p class="number"><?php echo $row2['CustomerNumber']; ?></p>
</div>