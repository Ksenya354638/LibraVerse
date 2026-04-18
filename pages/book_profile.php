<?php
session_start(); // Сесія завжди на самому початку

// Налаштування підключення до БД через змінні оточення (для Render/Aiven)
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<div class='validation-msg'><h2>Помилка підключення до бази даних</h2></div>");
}

// Перевірка авторизації
if (isset($_SESSION['LibrarianID'])) {
    $book = null;
    
    if (isset($_GET['BookID'])) {
        $bookID = $_GET['BookID'];

        // Отримання даних про книгу та автора (PDO версія)
        $stmt = $conn->prepare("SELECT b.*, a.Name, a.Surname, a.AuthorID 
                                 FROM books b 
                                 JOIN authors a ON b.AuthorID = a.AuthorID 
                                 WHERE b.BookID = ?");
        $stmt->execute([$bookID]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        // Видалення книги
        if (isset($_POST['delete'])) {
            $deleteStmt = $conn->prepare("DELETE FROM books WHERE BookID = ?");
            $deleteStmt->execute([$bookID]);
            header("Location: ./books_list.php?msg=deleted");
            exit;
        }

        // Видача книги клієнту
        if (isset($_POST['provide'])) {
            if (isset($_SESSION['CustomerID'])) {
                $customerID = $_SESSION['CustomerID'];
                $librarianID = $_SESSION['LibrarianID'];
                $receiptDate = date("Y-m-d");

                // ВАЖЛИВО: Переконайтеся, що в БД назва колонки CustomerID (латиницею)
                $provideStmt = $conn->prepare("INSERT INTO booksProvision (BookID, CustomerID, LibrarianID, ReceiptDate, ReturnDate) 
                                               VALUES (?, ?, ?, ?, '0')");
                $provideStmt->execute([$bookID, $customerID, $librarianID, $receiptDate]);

                // Оновлення статусу книги
                $updateStmt = $conn->prepare("UPDATE books SET Status = 'на руках' WHERE BookID = ?");
                $updateStmt->execute([$bookID]);

                unset($_SESSION['CustomerID']);
                header("Location: ./provision_list.php");
                exit;
            } else {
                // Якщо клієнта не обрано, перенаправляємо на список клієнтів
                header("Location: ./customers_list.php?action=select_for_book&BookID=" . $bookID);
                exit;
            }
        }
    }

    // Логаут
    if (isset($_GET['logOut'])) {
        session_destroy();
        header("Location: ../index.php"); // Шлях до вашої сторінки входу
        exit;
    }
?>
<!DOCTYPE html>
<html lang="uk_UA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link href="https://fonts.cdnfonts.com/css/roboto" rel="stylesheet">
    <title>LibraVerse - Профіль книги</title>
</head>
<body>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu">
                    <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
                </button>
                <div class="navbar-logo">
                    <img src="../images/logo.svg" alt="логотип">
                    <a href="./home.php" id="main">LibraVerse</a>
                </div>
            </div>