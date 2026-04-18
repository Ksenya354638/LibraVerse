<?php
session_start();

// Параметри підключення до БД (MySQL/Render)
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Помилка підключення до бази даних");
}

if(isset($_SESSION['LibrarianID'])) {
    if(isset($_GET['LibrarianID'])) {
        $librarianID = $_GET['LibrarianID'];

        // 1. Отримуємо дані працівника
        $stmt1 = $conn->prepare("SELECT * FROM librarians WHERE LibrarianID = ?");
        $stmt1->execute([$librarianID]);
        $librarian = $stmt1->fetch(PDO::FETCH_ASSOC);

        // 2. Отримуємо список виданих ним книг
        // Зверніть увагу: я виправив СustomerID (кирилична 'С' на латинську 'C')
        $stmt2 = $conn->prepare("SELECT bp.ProvisionID, b.BookID, b.Title, 
                                        c.CustomerID, c.FirstName, c.Surname, 
                                        c.PhoneNumber, bp.ReceiptDate, bp.ReturnDate 
                                 FROM booksProvision bp 
                                 JOIN books b ON bp.BookID = b.BookID 
                                 JOIN customers c ON bp.CustomerID = c.CustomerID 
                                 WHERE bp.LibrarianID = ?");
        $stmt2->execute([$librarianID]);
        $provisions = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // 3. Обробка видалення
        if(isset($_POST['delete'])) {
            $stmt_del = $conn->prepare("DELETE FROM librarians WHERE LibrarianID = ?");
            $stmt_del->execute([$librarianID]);
            // Після видалення краще перенаправити на список
            header("Location: librarians_list.php?deleted=success");
            exit;
        }
    }

    if (isset($_GET['logOut'])){
        session_destroy();
        header("Location: ../index.php");
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
    <title>Профіль працівника | LibraVerse</title>
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

    <?php if (isset($librarian) && $librarian): ?>
    <div class="container main-content librarian">
        <div class="col-lg-12 profile">
            <div class="book-descript col-lg-9">
                <h1 class="author-name">
                    <?php echo "{$librarian['Surname']} {$librarian['FirstName']} {$librarian['ParentalName']}"; ?> 
                    <small>(ID = <?php echo $librarian['LibrarianID']; ?>)</small>
                </h1>
                <p><b>Адреса: </b><?php echo htmlspecialchars($librarian['Address']); ?></p>
                <p><b>Телефон: </b><?php echo htmlspecialchars($librarian['PhoneNumber']); ?></p>
                <p><b>Дата народження: </b><?php echo $librarian['BirthDate']; ?></p>
                <p><b>Прийнятий на роботу: </b><?php echo $librarian['EmploymentDate']; ?></p>
                <p><b>Посада: </b><?php echo htmlspecialchars($librarian['Position']); ?></p>
            </div>
            <div class="buttons right col-lg-3">
                <form method="POST" onsubmit="return confirm('Ви впевнені, що хочете видалити цього працівника?');">
                    <input class="delete" type="submit" name="delete" value="Видалити працівника">
                </form>
            </div>
        </div>

        <div class="table col-lg-12" style="margin-top: 30px;">
            <h2>Книги, видані цим працівником:</h2>
            <table class="result-table col-lg-12 table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Книга</th>
                        <th>Клієнт</th>
                        <th>Дата видачі</th>
                        <th>Дата повернення</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($provisions as $row): ?>
                    <tr>
                        <td><?php echo $row['ProvisionID']; ?></td>
                        <td><a href="book_profile.php?BookID=<?php echo $row['BookID']; ?>"><?php echo htmlspecialchars($row['Title']); ?></a></td>
                        <td><a href="customer_profile.php?CustomerID=<?php echo $row['CustomerID']; ?>"><?php echo "{$row['FirstName']} {$row['Surname']}"; ?></a></td>
                        <td><?php echo $row['ReceiptDate']; ?></td>
                        <td><?php echo $row['ReturnDate'] ?: '<span class="text-warning">не повернуто</span>'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php elseif(isset($librarianID)): ?>
        <div class="container error-msg text-center">
            <img src="../images/error.svg" alt="error">
            <h1>Працівника з ID <?php echo htmlspecialchars($librarianID); ?> не знайдено</h1>
            <p><a href="librarians_list.php" class="btn btn-primary">До списку працівників</a></p>
        </div>
    <?php endif; ?>

    <footer class="footer col-lg-12">
        <div class="col-lg-9 footer-left">
            <p>Слідкуйте за нами:</p>
            <a href="#"><img src="../images/icon_facebook.svg" alt="фейсбук"></a>
            <a href="#"><img src="../images/icon-instagram.svg" alt="інстаграм"></a>
            <a href="#"><img src="../images/icon-twitterx.svg" alt="ікс"></a>
        </div>
        <div class="col-lg-3">
            <p>Зв’яжіться з нами: +380-88-675-89-12</p>
        </div>
        <div class="col-lg-12 text-center">
            <p>© 2026 LibraVerse. Всі права захищені.</p>
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</body>
</html>
<?php 
} else { 
    // Якщо не авторизований
    header("Location: ../index.php");
    exit;
} 
?>