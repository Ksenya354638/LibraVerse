<?php
session_start();

// Отримання параметрів з оточення
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$port = getenv('DB_PORT') ?: '3306'; 

try {
    // Вказуємо порт прямо у рядку підключення (DSN)
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_TIMEOUT => 7, // Збільшуємо таймаут для складних запитів
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    // Виводимо системну помилку лише для налагодження, якщо потрібно
    die("Помилка підключення до бази даних. Спробуйте оновити сторінку.");
}

if(isset($_SESSION['LibrarianID'])) {
    
    // 1. Оптимізоване отримання статистики одним запитом
    $stats = $conn->query("
        SELECT 
            SUM(CASE WHEN ReturnDate IS NULL OR ReturnDate = '' OR ReturnDate = '0' THEN 1 ELSE 0 END) as not_returned,
            SUM(CASE WHEN ReturnDate IS NOT NULL AND ReturnDate != '' AND ReturnDate != '0' THEN 1 ELSE 0 END) as returned,
            COUNT(*) as total
        FROM booksProvision
    ")->fetch(PDO::FETCH_ASSOC);

    $count_not_returned = $stats['not_returned'] ?? 0;
    $count_returned = $stats['returned'] ?? 0;
    $count_all = $stats['total'] ?? 0;

    // 2. Формування основного запиту
    $query = "SELECT bp.ProvisionID, bp.BookID, bp.CustomerID, b.Title, b.AuthorID, 
                     a.Name AS aName, a.Surname AS aSurname, 
                     c.FirstName, c.ParentalName, c.Surname AS cSurname, c.PhoneNumber, 
                     bp.ReceiptDate, bp.ReturnDate 
              FROM booksProvision bp
              JOIN books b ON b.BookID = bp.BookID 
              JOIN authors a ON b.AuthorID = a.AuthorID 
              JOIN customers c ON c.CustomerID = bp.CustomerID";

    // 3. Фільтрація
    $filter = "";
    if(isset($_POST['select'])) {
        $sort_by = $_POST['select'];
        if($sort_by === 'returned') {
            $filter = " WHERE bp.ReturnDate IS NOT NULL AND bp.ReturnDate != '' AND bp.ReturnDate != '0'";
        } elseif ($sort_by === 'not_returned') {
            $filter = " WHERE bp.ReturnDate IS NULL OR bp.ReturnDate = '' OR bp.ReturnDate = '0'";
        }
    }
    
    $full_query = $query . $filter . " ORDER BY bp.ReceiptDate DESC";
    $provisions = $conn->query($full_query)->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Видача книг | LibraVerse</title>
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
        <div class="table-header">
            <h1 class="text-center">Журнал видачі книг</h1>
        </div>

        <div class="row info-stats" style="margin-bottom: 20px;">
            <div class="col-md-4">
                <div class="well text-center" style="background-color: #fcf8e3;">
                    <h4>Не повернуто</h4>
                    <span class="h3 text-danger"><?php echo $count_not_returned; ?></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="well text-center" style="background-color: #dff0d8;">
                    <h4>Повернуто</h4>
                    <span class="h3 text-success"><?php echo $count_returned; ?></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="well text-center">
                    <h4>Всього видач</h4>
                    <span class="h3"><?php echo $count_all; ?></span>
                </div>
            </div>
        </div>

        <div class="row" style="margin-bottom: 20px;">
            <div class="col-lg-12">
                <form class="btn-group btn-group-justified" method="POST">
                    <div class="btn-group"><button type="submit" name="select" value="not_returned" class="btn btn-default">Тільки заборгованості</button></div>
                    <div class="btn-group"><button type="submit" name="select" value="returned" class="btn btn-default">Тільки повернуті</button></div>
                    <div class="btn-group"><button type="submit" name="select" value="all" class="btn btn-primary">Усі записи</button></div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr class="active">
                                <th>Книга</th>
                                <th>Автор</th>
                                <th>Клієнт</th>
                                <th>Видано</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($provisions as $row): 
                                $is_returned = ($row['ReturnDate'] && $row['ReturnDate'] != '0');
                            ?>
                            <tr class="<?php echo $is_returned ? '' : 'warning'; ?>">
                                <td><a href="book_profile.php?BookID=<?php echo $row['BookID']; ?>"><?php echo htmlspecialchars($row['Title']); ?></a></td>
                                <td><?php echo htmlspecialchars($row['aName'] . " " . $row['aSurname']); ?></td>
                                <td><a href="customer_profile.php?CustomerID=<?php echo $row['CustomerID']; ?>"><?php echo htmlspecialchars($row['FirstName'] . " " . $row['cSurname']); ?></a></td>
                                <td><?php echo $row['ReceiptDate']; ?></td>
                                <td>
                                    <?php echo $is_returned ? $row['ReturnDate'] : '<span class="label label-danger">На руках</span>'; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
    header("Location: ../index.php");
    exit;
} ?>