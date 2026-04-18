<?php
session_start();

// Підключення до БД (використовуйте ваші налаштування з Render/Aiven)
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $db_error = true;
}

if(isset($_SESSION['LibrarianID'])) {
    $success = false;
    if(isset($_POST['add-customer'])){
        $query = $conn->prepare("INSERT INTO customers (FirstName, ParentalName, Surname, Address, PhoneNumber, Employment) 
                                 VALUES (?, ?, ?, ?, ?, ?)");
        $query->execute([
            $_POST['fname'], 
            $_POST['pname'], 
            $_POST['sname'], 
            $_POST['address'], 
            $_POST['phone'], 
            $_POST['employment']
        ]);
        $success = true;
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
    <title>Реєстрація читача | LibraVerse</title>
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

    <?php if($success): ?>
    <div class='validation-msg done'>
        <img src='../images/done.svg' alt='done'>
        <h2 class='validation-text'>Клієнта успішно додано до системи</h2>     
    </div>
    <?php endif; ?>

    <div class="container main-content add">
        <div class="col-lg-6 form-container">
            <form class="book-form" method="POST">
                <h1 class="text-center">Реєстрація користувача</h1>
                <label>Ім'я</label>
                <input type="text" class="form-input" name="fname" required>
                <label>По батькові</label>
                <input type="text" class="form-input" name="pname" required>
                <label>Прізвище</label>
                <input type="text" class="form-input" name="sname" required>
                <label>Адреса</label>
                <input type="text" class="form-input" name="address" required>
                <label>Телефон</label>
                <input type="text" class="form-input" name="phone" placeholder="+380..." required>
                <label>Місце роботи/навчання</label>
                <input type="text" class="form-input" name="employment" required>
                <div class="line">
                    <input class="submit" type="submit" name="add-customer" value="Зареєструвати">
                </div>
            </form>
        </div>
        <div class="col-lg-6 image hidden-xs">
            <img src="../images/add_user.png" class="img-responsive" alt="reader">
        </div>
    </div>

    <footer class="footer col-lg-12 text-center">
        <p>© 2026 LibraVerse. Всі права захищені.</p>
    </footer>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
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
</body>
</html>
<?php
} else {
    // Сторінка помилки авторизації
    include 'error_auth.php'; // Рекомендую винести цей HTML в окремий файл, щоб не дублювати
}
?>