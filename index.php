<?php
    $db_file = "./library.db";
    $conn = new SQLite3($db_file);
    if (!$conn) {
        echo "<div class='validation-msg'>
                <img src='./images/error.svg' alt='error icon'>
                <h2 class='validation-text'>Помилка! Не вдалося під'єднатися до бази даних</h2>     
              </div>";
    };

    session_start();
    if(isset($_POST['logIn'])) {
        $phoneNumber= $_POST['phoneNumber'];
        $password= $_POST['password'];
        $query_create = $conn->prepare("SELECT LibrarianID, FirstName, ParentalName,PhoneNumber,Password FROM 
                                        librarians WHERE PhoneNumber=:phoneNumber AND Password=:password;");
        $query_create->bindValue(':phoneNumber', $phoneNumber, SQLITE3_TEXT);
        $query_create->bindValue(':password', $password, SQLITE3_TEXT);
        $query_create->execute();
        $result = $query_create->execute();
        $librarian = $result->fetchArray(SQLITE3_ASSOC);
        if  ($librarian) {
            $_SESSION['LibrarianID'] = $librarian['LibrarianID'];
            header("Location: ./pages/home.php");
        }   else {
            echo "<div class='validation-msg'>
                    <img src='./images/error.svg' alt='error icon'>
                    <h2 class='validation-text'>Помилка! Не вдалося авторизуватися за уведеними даними</h2>     
                  </div>";
        }   
    }
?>
<!DOCTYPE html>
<html lang="uk_UA">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link href="https://fonts.cdnfonts.com/css/roboto" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./js/script.js"></script>
    <title>LibraVerse</title>
</head>
<body>
    <div class="main-content auhorization">
        <form class="book-form authorization" method="POST">
            <h1 class="text-center">Вхід в акаунт працівника</h1>
            <label for="phoneNumber">Номер мобільного телефону</label><br>
            <input type="text" class="form-input" id="phoneNumber" name="phoneNumber"  placeholder="+380ХХХХХХХХХ" required><br>
            <label for="password">Пароль</label><br>
            <input type="password" class="form-input" id="password" name="password"  placeholder="Уведіть пароль" required><br>        
            <div class="line">
                <input class="submit" type="submit" name="logIn" value="Увійти">
            </div>
        </form>        
    </div>
    <footer class="footer col-lg-12">
        <div class="col-lg-9 footer-left">
            <p>Слідкуйте за нами:</p>
            <a href="https://www.facebook.com/?locale=uk_UA">
                <img src="./images/icon_facebook.svg" alt="фейсбук">
            </a>
            <a href="https://www.instagram.com/">
                <img src="./images/icon-instagram.svg" alt="інстаграм">
            </a>
            <a href="https://twitter.com/?lang=uk">
                <img src="./images/icon-twitterx.svg" alt="ікс">
            </a>
        </div>
        <div class="col-lg-3">
            <p>Зв’яжіться з нами: +380-88-675-89-12</p>
        </div>
        <div class="col-lg-12 text-center">
            <p>© 2024 LibraVerse. Всі права захищені.</p>
        </div>
    </footer>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="./js/bootstrap.min.js"></script>
</body>
</html>
<?php
 $conn->close();
 ?>