<?php
session_start();

$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$port = getenv('DB_PORT') ?: '3306';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { 
    die("Помилка БД: " . $e->getMessage()); 
}

if(isset($_SESSION['LibrarianID'])) {
    $success = false;
    $error_msg = "";

    if(isset($_POST['add-librarian'])){
        try {
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            $query = $conn->prepare("INSERT INTO librarians (FirstName, ParentalName, Surname, Address, PhoneNumber, BirthDate, EmploymentDate, Position, Password) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $query->execute([
                $_POST['fname'], 
                $_POST['pname'], 
                $_POST['sname'], 
                $_POST['address'], 
                $_POST['phone'], 
                $_POST['birthDate'], 
                $_POST['employmentDate'], 
                $_POST['position'], 
                $hashed_password
            ]);
            $success = true;
        } catch (PDOException $e) {
            $error_msg = "Не вдалося додати працівника: " . $e->getMessage();
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
    <title>Новий працівник | LibraVerse</title>
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

    <div class="container" style="margin-top: 30px;">
        <?php if($success): ?>
            <div class='alert alert-success text-center'>
                <h2>Успішно! Працівника додано до системи.</h2>
                <a href="librarians_list.php" class="btn btn-success">До списку</a>
            </div>
        <?php endif; ?>

        <?php if($error_msg): ?>
            <div class='alert alert-danger'><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Реєстрація працівника</h3></div>
                    <div class="panel-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label>Ім'я</label>
                                    <input type="text" class="form-control" name="fname" required>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>По батькові</label>
                                    <input type="text" class="form-control" name="pname" required>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Прізвище</label>
                                    <input type="text" class="form-control" name="sname" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Адреса</label>
                                <input type="text" class="form-control" name="address" required>
                            </div>

                            <div class="form-group">
                                <label>Телефон</label>
                                <input type="text" class="form-control" name="phone" placeholder="+380..." required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Дата народження</label>
                                    <input type="date" class="form-control" name="birthDate" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Дата прийому</label>
                                    <input type="date" class="form-control" name="employmentDate" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Посада</label>
                                <select name="position" class="form-control">
                                    <option value="бібліотекар">Бібліотекар</option>
                                    <option value="завідуючий">Завідуючий</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Пароль</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>

                            <button type="submit" name="add-librarian" class="btn btn-primary btn-block">Зареєструвати</button>
                        </form>
                    </div>
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
</body>
</html>
<?php
} else { header("Location: ../index.php"); exit; }
?>