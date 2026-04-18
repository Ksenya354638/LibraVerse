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
    if(isset($_GET['BookID'])) {
    $bookID= $_GET['BookID'];
    $query_create = $conn->prepare("SELECT books.BookCover, books.Title, authors.AuthorID, authors.Name, authors.Surname, books.Publisher, books.Year, books.Category, books.Price, books.Condition, books.Status, books.Abstract FROM books JOIN authors ON books.AuthorID=authors.AuthorID WHERE books.BookID=:bookID;");
    $query_create->bindValue(':bookID', $bookID, SQLITE3_INTEGER);
    $query_create->execute();
    $result = $query_create->execute();
    $book = $result->fetchArray(SQLITE3_ASSOC);
    if(isset($_POST['delete'])) {
        $query_create = $conn->prepare("DELETE FROM books WHERE BookID=:bookID;");
        $query_create->bindValue(':bookID', $bookID, SQLITE3_INTEGER);
        $query_create->execute();
        echo "<div class='validation-msg done'>
                <img src='../images/done.svg' alt='error icon'>
                <h2 class='validation-text'>Книгу успішно видалено з каталогу</h2>     
              </div>";
    }
    if(isset($_POST['provide'])) {
        $_SESSION['BookID'] = $bookID;
        if(isset($_SESSION['CustomerID']) && isset($_SESSION['LibrarianID'])) {
            $BookID = $_SESSION['BookID'];
            $CustomerID = $_SESSION['CustomerID'];
            $LibrarianID = $_SESSION['LibrarianID'];
            $ReceiptDate = date("Y-m-d");
            $ReturnDate = 0;

            $query_create = $conn->prepare("INSERT INTO booksProvision(BookID,СustomerID,LibrarianID,ReceiptDate,ReturnDate) VALUES (:BookID, :СustomerID, :LibrarianID, :ReceiptDate, :ReturnDate )");
            $query_create->bindValue(':BookID', $BookID, SQLITE3_INTEGER);
            $query_create->bindValue(':СustomerID', $CustomerID, SQLITE3_INTEGER);
            $query_create->bindValue(':LibrarianID', $LibrarianID, SQLITE3_INTEGER);
            $query_create->bindValue(':ReceiptDate', $ReceiptDate, SQLITE3_TEXT);
            $query_create->bindValue(':ReturnDate', $ReturnDate, SQLITE3_TEXT);
            $query_create->execute();
            $query_create2 = $conn->prepare("UPDATE books SET Status='на руках' WHERE BookID=:BookID;");
            $query_create2->bindValue(':BookID', $BookID, SQLITE3_INTEGER);
            $query_create2->execute();
            echo "<div class='validation-msg done'>
                        <img src='../images/done.svg' alt='error icon'>
                        <h2 class='validation-text'>Книгу успішно видано користувачу</h2>     
                  </div>";
            unset($_SESSION['CustomerID']);
            unset($_SESSION['BookID']);

        } else {
            echo "Обреіть клієнта, якому небхідно вибрати книгу";
            header("Location: ./customers_list.php");
            exit;
        }
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
    <?php if ($book) { ?>
    <div class="main-content profile"> 
        <div class="book-cover col-lg-3">
            <img src="<?php echo $book['BookCover'];?>" alt="фото обкладинки книги">
        </div>
        <div class="col-lg-9">
            <div class="book-descript col-lg-8">
                <h1><?php echo $book['Title'];?></h1>
                <h2><a href="http://localhost/LibraVerse/pages/author_profile.php?AuthorID=<?php echo $book['AuthorID']; ?>"><?php echo $book['Name'];?> <?php echo $book['Surname'];?></a></h2>
                <p><b>Видавець: </b><?php echo $book['Publisher'];?></p>
                <p><b>Рік видання: </b><?php echo $book['Year'];?></p>
                <p><b>Категорія: </b><?php echo $book['Category'];?></p>
                <p><b>Ціна: </b><?php echo $book['Price'];?> грн</p>
                <p><b>Стан: </b><?php echo $book['Condition'];?></p>
            </div>
            <div class="buttons col-lg-4">
                <form method="POST">
                    <input class="delete" type="submit" name="delete" value="видалити з каталогу"><br><br>
                    <input class="provide" type="submit" name="provide" value="видати">
                    <p class="status"><?php echo $book['Status']; ?></p>
                </form>
            </div>
            <div class="abstract col-lg-12">
                <p><?php echo $book['Abstract']; ?></p>
            </div>
        </div>
    </div>
    <?php } else { ?>
        <div class="main-content error-msg" id="main-content">
        <img src="../images/error.svg" alt="error icon">
        <div class="error-text">
            <h1>Помилка! Книги з таким ID не знайдено</h1>
            <p>Поверніться до <a href="http://localhost/LibraVerse/pages/books_list.php">списку книг</a></p>
        </div>
        </div> 
    <?php } ?>
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
<?php } else { ?>
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
                       <a href="#" id="main">LibraVerse</a>
                   </div>
               </div>
               <div class="collapse navbar-collapse" id="menu">
                   <ul class="nav navbar-nav navbar-right text-center">
                     <li><a href="http://localhost/LibraVerse/" id="main">Головна</a></li>
                     <li><a href="http://localhost/LibraVerse/pages/customers_list.php" id="customers">Клієнти</a></li>
                     <li><a href="http://localhost/LibraVerse/pages/books_list.php" id="books">Книги</a></li>
                     <li><a href="http://localhost/LibraVerse/pages/author_list.php" id="authors">Автори</a></li> 
                     <li><a href="http://localhost/LibraVerse/pages/librarians_list.php" id="librarians">Працівники</a></li>
                     <li><a href="http://localhost/LibraVerse/pages/provision_list.php" id="provision">Видача книг</a></li>
                   </ul>
               </div>
           </div>
       </nav>
        <div class="main-content error-msg" id="main-content">
            <img src="../images/error.svg" alt="error icon">
            <div class="error-text">
                <h1>Помилка! Не отримано ID книги</h1>
                <p>Поверніться до <a href="http://localhost/LibraVerse/pages/books_list.php">списку книг</a></p>
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
    }} else {
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
