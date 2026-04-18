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
    $query = "SELECT books.BookID, authors.AuthorID, books.BookCover, books.Title, authors.Name, authors.Surname, books.Status FROM books JOIN authors ON books.AuthorID=authors.AuthorID";
    if(isset($_POST['sort'])) {
        $sort_by = $_POST['sort'];
        if ($sort_by === 'fiction') {
        $query .= " WHERE books.Category='художня';";
        } elseif ($sort_by === 'medical') {
            $query .= " WHERE books.Category='медична';";
        } elseif ($sort_by === 'technical') {
            $query .= " WHERE books.Category='технічна';";
        } elseif ($sort_by === 'economic') {
            $query .= " WHERE books.Category='економічна';";
        } elseif ($sort_by === 'computer') {
            $query .= " WHERE books.Category='компютерна';";
        } elseif ($sort_by === 'natural') {
            $query .= " WHERE books.Category='природознавча';";
        } elseif ($sort_by === 'legal') {
            $query .= " WHERE books.Category='юридична';";
        } elseif ($sort_by === 'general') {
            $query .= " WHERE books.Category='загальна';";
        }
    }
    $books_result = $conn->query($query);
    if(isset($_POST['provide'])) {
        $_SESSION['BookID'] = $_POST['BookID'];
        if(isset($_SESSION['CustomerID']) && isset($_SESSION['LibrarianID'])) {
            $BookID = $_SESSION['BookID'];
            $CustomerID = $_SESSION['CustomerID'];
            $LibrarianID = $_SESSION['LibrarianID'];
            $ReceiptDate = date("Y-m-d");
            $ReturnDate = 0;

            $query_create = $conn->prepare("INSERT INTO booksProvision(BookID,СustomerID,LibrarianID,ReceiptDate,ReturnDate) VALUES (:BookID, :СustomerID, :LibrarianID, :ReceiptDate, :ReturnDate )");
            $query_create->bindValue(':BookID', $BookID, SQLITE3_INTEGER);
            $query_create->bindValue(':СustomerID', $_SESSION['CustomerID'], SQLITE3_INTEGER);
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
    <div class="main-content">
        <div class="table-header">
            <h1 class="text-center">Книжковий каталог</h1>
        </div>
        <div>
            <div class="col-lg-3">
                <div class="side-menu">
                    <form action="" method="POST">
                        <h3 class="">Оберіть категорію</h3>
                        <button  type="submit" name="sort" value="fiction">художня література</button><br>
                        <button  type="submit" name="sort" value="medical">медична література</button><br>
                        <button  type="submit" name="sort" value="technical">технічна література</button><br>
                        <button  type="submit" name="sort" value="economic">економічна література</button><br>
                        <button  type="submit" name="sort" value="computer">комп'ютерна література</button><br>
                        <button  type="submit" name="sort" value="natural'">природознавча література</button><br>
                        <button  type="submit" name="sort" value="legal">юридична література</button><br>
                        <button  type="submit" name="sort" value="general">загальноосвітня література</button><br>
                    </form>
                </div>
                <div class="button">
                    <a href="http://localhost/LibraVerse/pages/new_book.php" class="add">додати книгу</a>
                </div>                
            </div>
            <div class="col-lg-9 row books-list">
            <?php while ($books = $books_result->fetchArray(SQLITE3_ASSOC)) { ?>
                <div class="about-book col-lg-4">
                    <div class="book-cover">
                        <img src="<?php echo $books['BookCover']; ?>" alt="фото обкладинки">
                    </div>
                    <div class="book-description">
                        <h3><a href="http://localhost/LibraVerse/pages/book_profile.php?BookID=<?php echo $books['BookID']; ?>"><?php echo $books['Title']; ?></a></h3>
                        <p class="author"><a href="http://localhost/LibraVerse/pages/author_profile.php?AuthorID=<?php echo $books['AuthorID']; ?>"><?php echo $books['Name']; ?> <?php echo $books['Surname']; ?></a></p>
                        <div>
                            <div class="col-lg-6">
                                <?php if ($books['Status'] === 'в наявності') {?>
                                    <form method="POST">
                                        <input type="hidden" name="BookID" value="<?php echo $books['BookID']; ?>">
                                        <button class="provide" type="submit" name="provide" value="Видати">Видати</button>
                                    </form>
                                <?php } ?>
                            </div>
                            <div class="col-lg-6">
                                <p class="status"><?php echo $books['Status']; ?></p>
                            </div>

                        </div>
                    </div>                    
                </div>
            <?php }?>
            </div>
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