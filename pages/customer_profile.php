<?php
session_start();
// Підключення до БД (PDO)
$host = getenv('DB_HOST'); $port = getenv('DB_PORT'); $dbname = getenv('DB_NAME');
$user = getenv('DB_USER'); $pass = getenv('DB_PASSWORD');

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Помилка БД"); }

if(isset($_SESSION['LibrarianID'])) {
    if(isset($_GET['CustomerID'])) {
        $customerID = $_GET['CustomerID'];

        // 1. Отримання даних клієнта
        $stmt = $conn->prepare("SELECT * FROM customers WHERE CustomerID = ?");
        $stmt->execute([$customerID]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Книги, що зараз на руках (ReturnDate = 0)
        // ВИПРАВЛЕНО: назва таблиці booksprovision
        $stmtActive = $conn->prepare("SELECT bp.ProvisionID, bp.BookID, bp.LibrarianID, b.Title, l.FirstName, l.Surname, bp.ReceiptDate 
            FROM booksprovision bp 
            JOIN books b ON bp.BookID = b.BookID 
            JOIN librarians l ON bp.LibrarianID = l.LibrarianID 
            WHERE bp.CustomerID = ? AND bp.ReturnDate = '0'");
        $stmtActive->execute([$customerID]);

        // 3. Історія (ReturnDate != 0)
        // ВИПРАВЛЕНО: назва таблиці booksprovision
        $stmtHistory = $conn->prepare("SELECT bp.ProvisionID, bp.BookID, b.Title, l.FirstName, l.Surname, bp.ReceiptDate, bp.ReturnDate 
            FROM booksprovision bp 
            JOIN books b ON bp.BookID = b.BookID 
            JOIN librarians l ON bp.LibrarianID = l.LibrarianID 
            WHERE bp.CustomerID = ? AND bp.ReturnDate != '0'
            ORDER BY bp.ReturnDate DESC");
        $stmtHistory->execute([$customerID]);

        // --- ЛОГІКА ДІЙ ---

        // Видалення клієнта
        if(isset($_POST['delete'])) {
            $del = $conn->prepare("DELETE FROM customers WHERE CustomerID = ?");
            $del->execute([$customerID]);
            header("Location: ./customers_list.php?msg=deleted");
            exit;
        }

        // Повернення книги
        if(isset($_POST['return'])) {
            $provID = $_POST['return'];
            $bookID = $_POST['bookID'];
            $now = date("Y-m-d");

            // ВИПРАВЛЕНО: назва таблиці booksprovision
            $conn->prepare("UPDATE booksprovision SET ReturnDate = ? WHERE ProvisionID = ?")->execute([$now, $provID]);
            $conn->prepare("UPDATE books SET Status = 'в наявності' WHERE BookID = ?")->execute([$bookID]);
            header("Location: ./customer_profile.php?CustomerID=$customerID");
            exit;
        }

        // Початок процесу видачі книги
        if(isset($_POST['provide'])) {
            $_SESSION['CustomerID'] = $customerID;
            header("Location: ./books_list.php"); 
            exit;
        }
?>
<!DOCTYPE html>
<html lang="uk_UA">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <title>Профіль клієнта | LibraVerse</title>
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
            <div class="collapse navbar-collapse" id="menu">
                <ul class="nav navbar-nav navbar-right text-center">
                  <li><a href="./home.php">Головна</a></li>
                  <li><a href="./customers_list.php">Клієнти</a></li>
                  <li><a href="./books_list.php">Книги</a></li>
                  <li><a href="./author_list.php">Автори</a></li> 
                  <li><a href="./librarians_list.php">Працівники</a></li>
                  <li><a href="./provision_list.php">Видача книг</a></li>
                  <li><a href="?logOut=1" id="logOut">Вийти</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container main-content">
        <?php if ($customer): ?>
            <div class="row profile-header">
                <div class="col-md-8">
                    <h1><?php echo htmlspecialchars($customer['Surname'] . " " . $customer['FirstName']); ?></h1>
                    <p><b>Адреса:</b> <?php echo htmlspecialchars($customer['Address']); ?></p>
                    <p><b>Телефон:</b> <?php echo htmlspecialchars($customer['PhoneNumber']); ?></p>
                    <p><b>Робота:</b> <?php echo htmlspecialchars($customer['Employment']); ?></p>
                </div>
                <div class="col-md-4 text-right">
                    <form method="POST" onsubmit="return confirm('Видалити цього клієнта?')">
                        <button type="submit" name="delete" class="btn btn-danger">Видалити профіль</button>
                    </form>
                    <form method="POST" class="mt-2">
                        <button type="submit" name="provide" class="btn btn-success">Видати нову книгу</button>
                    </form>
                </div>
            </div>

            <hr>

            <h3>Книги на руках:</h3>
            <table class="table table-bordered">
                <tr class="info">
                    <th>Книга</th><th>Дата видачі</th><th>Дія</th>
                </tr>
                <?php while ($row = $stmtActive->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><a href="./book_profile.php?BookID=<?php echo $row['BookID']; ?>"><?php echo htmlspecialchars($row['Title']); ?></a></td>
                    <td><?php echo $row['ReceiptDate']; ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="bookID" value="<?php echo $row['BookID']; ?>">
                            <button type="submit" name="return" value="<?php echo $row['ProvisionID']; ?>" class="btn btn-xs btn-warning">Повернуто</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>

            <h3 class="mt-5">Історія:</h3>
            <table class="table table-condensed">
                <tr><th>Книга</th><th>Видано</th><th>Повернено</th></tr>
                <?php while ($row = $stmtHistory->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['Title']); ?></td>
                    <td><?php echo $row['ReceiptDate']; ?></td>
                    <td><?php echo $row['ReturnDate']; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>

        <?php else: ?>
            <div class="alert alert-danger">Клієнта не знайдено!</div>
        <?php endif; ?>
    </div>
    <footer class="footer text-center">
        <p>© 2026 LibraVerse. Всі права захищені.</p>
    </footer>
</body>
</html>
<?php 
    } 
} else { 
    header("Location: ../index.php"); 
    exit;
} 
?>