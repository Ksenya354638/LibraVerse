<?php
    $db_file = "../library.db";
    $conn = new SQLite3($db_file);
    if (!$conn) {
        echo "<div class='validation-msg'>
                <img src='../images/error.svg' alt='error icon'>
                <h2 class='validation-text'>Помилка! Не вдалося під'єднатися до бази даних</h2>     
              </div>";
    };
    session_start();
    if(isset($_SESSION['LibrarianID'])) {
    if(isset($_FILES['cover'])) {
        $target_directory = "../images/books_img/";
        $target_file = $target_directory . basename($_FILES["cover"]["name"]);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        if(isset($_POST["cover"])) {
            $check = getimagesize($_FILES["cover"]["tmp_name"]);
            if($check !== false) {
                $uploadOk = 1;
            } else {
                $uploadOk = 0;
            }
        }
        if ($_FILES["cover"]["size"] > 500000) {
            $uploadOk = 0;
        }
        if($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg") {
            $uploadOk = 0;
        }
        if (file_exists($target_file)) {
            $uploadOk = 0;
        }
        if ($uploadOk == 0) {
        } else {
            if (move_uploaded_file($_FILES["cover"]["tmp_name"], $target_file)) {
            }
        }
    }
    if(isset($_POST['add-book'])){
        $title= $_POST['title'];
        $author= $_POST['author'];
        $publisher= $_POST['publisher'];
        $year= $_POST['year'];
        $category= $_POST['category'];
        $price= $_POST['price'];
        $condition= $_POST['condition'];
        $abstract= $_POST['abstract'];
        $status= "в наявності";
        $cover= $target_file;

        $query_create = $conn->prepare("INSERT INTO books (Title, AuthorID, Publisher, Year, Category, Price, Condition, Abstract, Status, BookCover) VALUES 
        (:title, :author, :publisher, :year, :category, :price, :condition, :abstract, :status, :cover);");
        $query_create->bindValue(':title',$title, SQLITE3_TEXT);
        $query_create->bindValue(':author',$author, SQLITE3_INTEGER);
        $query_create->bindValue(':publisher',$publisher, SQLITE3_TEXT);
        $query_create->bindValue(':year',$year, SQLITE3_INTEGER);
        $query_create->bindValue(':category',$category, SQLITE3_TEXT);
        $query_create->bindValue(':price',$price, SQLITE3_INTEGER);
        $query_create->bindValue(':condition',$condition, SQLITE3_INTEGER);
        $query_create->bindValue(':abstract',$abstract, SQLITE3_TEXT);
        $query_create->bindValue(':status',$status, SQLITE3_TEXT);
        $query_create->bindValue(':cover',$cover, SQLITE3_TEXT);
        $query_create->execute();

        echo "<div class='validation-msg done'>
                <img src='../images/done.svg' alt='error icon'>
                <h2 class='validation-text'>Книгу успішно додано до каталогу</h2>     
              </div>";
    }
    if (isset($_GET['logOut'])){
        session_unset();
        header("Location: ./librarian_authorization.php");
    }
?>
<!DOCTYPE html>
<html lang="uk_UA">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link href="https://fonts.cdnfonts.com/css/roboto" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/script.js"></script>
    <title>LibraVerse</title>
</head>
<body>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed visible-xs" data-toggle="collapse"
                 data-target="#menu" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <div class="navbar-logo">
                    <img src="../images/logo.svg" alt="логотип">
                    <a href="http://localhost/LibraVerse/pages/home.php" id="main">LibraVerse</a>
                </div>
            </div>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="nav navbar-nav navbar-right text-center">
                  <li><a href="http://localhost/LibraVerse/pages/home.php" id="main">Головна</a></li>
                  <li><a href="http://localhost/LibraVerse/pages/customers_list.php" id="customers">Клієнти</a></li>
                  <li><a href="http://localhost/LibraVerse/pages/books_list.php" id="books">Книги</a></li>
                  <li><a href="http://localhost/LibraVerse/pages/author_list.php" id="authors">Автори</a></li> 
                  <li><a href="http://localhost/LibraVerse/pages/librarians_list.php" id="librarians">Працівники</a></li>
                  <li><a href="http://localhost/LibraVerse/pages/provision_list.php" id="provision">Видача книг</a></li>
                  <li><a href="?logOut=<?php echo ($_SESSION['LibrarianID']); ?>" id="logOut">Вийти</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="main-content add">
        <div class="col-lg-6 form-container">
        <form class="book-form" method="POST" enctype="multipart/form-data">
                <h1 class="text-center">Додати нову книгу</h1>
                <label for="title">Назва</label><br>
                <input type="text" class="form-input" id="title" name="title"  placeholder="уведіть назву книги" required><br>
                <label for="author">Автор</label><br>
                <input type="number" class="form-input" id="author" name="author" placeholder="уведіть ID автора" required><br>
                <label for="publisher">Видавництво</label><br>
                <input type="text" class="form-input" id="publisher" name="publisher"  placeholder="уведіть назву видавництва книги"><br>
                <label for="year">Рік публікації</label><br>
                <input type="number" class="form-input" id="year" name="year"  placeholder="уведіть рік публікації книги"><br>
                <label for="category">Категорія книги</label><br>
                <div class="col-lg-12">
                    <div class="col-lg-4">
                        <div class="radio"><input class="radio" type="radio" id="category" name="category" value="художня"><p>художня</p></div>                        
                        <div class="radio"><input class="radio" type="radio" id="category" name="category" value="медична"><p>медична</p></div>   
                        <div class="radio"><input class="radio" type="radio" id="category" name="category" value="технічна"><p>технічна</p></div>   
                    </div>
                    <div class="col-lg-4">
                        <div class="radio"><input type="radio" id="category" name="category" value="економічна"><p>економічна</p></div>   
                        <div class="radio"><input type="radio" id="category" name="category" value="комп'ютерна"><p>комп'ютерна</p></div>   
                        <div class="radio"><input type="radio" id="category" name="category" value="природознавча"><p>природознавча</p></div>   
                    </div>
                    <div class="col-lg-4">
                        <div class="radio"><input type="radio" id="category" name="category" value="юридична"><p>юридична</p></div>   
                        <div class="radio"><input type="radio" id="category" name="category" value="загальна"><p>загальна</p></div>   
                    </div>
                </div>
                <label for="year">Ціна видання</label><br>
                <input type="number" class="form-input" id="price" name="price"  placeholder="уведіть ціну примірника книги"><br>
                <label for="condition">Стан книги</label><br>
                <div class="col-lg-12">
                    <div class="radio col-lg-4"><input type="radio" id="condition" name="condition" value="художня"><p>нова</p></div>
                    <div class="radio col-lg-4"><input type="radio" id="condition" name="condition" value="незначно пошкоджна"><p>незначно пошкоджна</p></div>
                    <div class="radio col-lg-4"><input type="radio" id="condition" name="condition" value="значно пошкоджена"><p>значно пошкоджена</p></div>            
                </div>
                <label for="abstract">Анотація</label><br>
                <textarea rows="4" class="form-input abstract" id="abstract" name="abstract" required> Уведіть текст анотації книги
                </textarea> <br>
                <label>Фото обкладинки</label>
                <label for="cover" class="custom-file-upload">Завантажити файл</label>
                <input type="file" class="file-upload" accept="image/jpeg, image/png, , image/jpg" id="cover" name="cover">
                <div class="line">
                    <input class="submit" type="submit" name="add-book" value="Додати книгу">
                </div>
                </form>
        </div>
        <div class="col-lg-6 image">
            <img src="../images/add_book.png" alt="зображення книги">
        </div>
    </div>
    <footer class="footer col-lg-12">
        <div class="col-lg-9 footer-left">
            <p>Слідкуйте за нами:</p>
            <a href="https://www.facebook.com/?locale=uk_UA">
                <img src="../images/icon_facebook.svg" alt="фейсбук">
            </a>
            <a href="https://www.instagram.com/">
                <img src="../images/icon-instagram.svg" alt="інстаграм">
            </a>
            <a href="https://twitter.com/?lang=uk">
                <img src="../images/icon-twitterx.svg" alt="ікс">
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
<script src="../js/bootstrap.min.js"></script>
</body>
</html>
<?php
    } else {
?>
<!DOCTYPE html>
<html lang="uk_UA">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link href="https://fonts.cdnfonts.com/css/roboto" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/script.js"></script>
    <title>LibraVerse</title>
</head>
<body>
    <div class="main-content error-msg" id="main-content">
        <img src="../images/error.svg" alt="error icon">
        <div class="error-text">
            <h1>Помилка! Ви не авторизовані</h1>
            <p>Поверніться до <a href="http://localhost/LibraVerse/">сторінки авторизації працівника</a></p>
        </div>
    </div>
    <footer class="footer col-lg-12">
        <div class="col-lg-9 footer-left">
            <p>Слідкуйте за нами:</p>
            <a href="https://www.facebook.com/?locale=uk_UA">
                <img src="../images/icon_facebook.svg" alt="фейсбук">
            </a>
            <a href="https://www.instagram.com/">
                <img src="../images/icon-instagram.svg" alt="інстаграм">
            </a>
            <a href="https://twitter.com/?lang=uk">
                <img src="../images/icon-twitterx.svg" alt="ікс">
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
<script src="../js/bootstrap.min.js"></script>
</body>
</html>
<?php
    }
 $conn->close();
 ?>