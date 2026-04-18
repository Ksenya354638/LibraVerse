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
    if(isset($_GET['CustomerID'])) {
    $customerID = $_GET['CustomerID'];
    $query_create1 = $conn->prepare("SELECT * FROM customers WHERE СustomerID=:customerID ;");
    $query_create1->bindValue(':customerID', $customerID, SQLITE3_INTEGER);
    $query_create1->execute();
    $result1 = $query_create1->execute();
    $customer = $result1->fetchArray(SQLITE3_ASSOC);

    $query_create2 = $conn->prepare("SELECT booksProvision.ProvisionID, booksProvision.BookID, booksProvision.LibrarianID, books.Title, librarians.FirstName, librarians.Surname, booksProvision.ReceiptDate FROM booksProvision JOIN books ON booksProvision.BookID=books.BookID JOIN librarians ON booksProvision.LibrarianID=librarians.LibrarianID WHERE booksProvision.СustomerID=:customerID AND booksProvision.ReturnDate=0;");
    $query_create2->bindValue(':customerID', $customerID, SQLITE3_INTEGER);
    $result2 = $query_create2->execute();

    $query_create3 = $conn->prepare("SELECT booksProvision.ProvisionID, booksProvision.BookID, booksProvision.LibrarianID, books.Title, librarians.FirstName, librarians.Surname, booksProvision.ReceiptDate, booksProvision.ReturnDate FROM booksProvision JOIN books ON booksProvision.BookID=books.BookID JOIN librarians ON booksProvision.LibrarianID=librarians.LibrarianID WHERE booksProvision.СustomerID=:customerID AND booksProvision.ReturnDate!=0;");
    $query_create3->bindValue(':customerID', $customerID, SQLITE3_INTEGER);
    $result3 = $query_create3->execute();


    if(isset($_POST['delete'])) {
        $query_create = $conn->prepare("DELETE FROM customers WHERE СustomerID=:customerID;");
        $query_create->bindValue(':customerID', $customerID, SQLITE3_INTEGER);
        $query_create->execute();
        echo "<div class='validation-msg done'>
                        <img src='../images/done.svg' alt='error icon'>
                        <h2 class='validation-text'>Користувача успішно видалено з системи</h2>     
                  </div>";
    }
    if(isset($_POST['provide'])) {
        $_SESSION['CustomerID'] = $customerID;
        if(isset($_SESSION['BookID']) && isset($_SESSION['LibrarianID'])) {
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
            echo "Обреіть книги, які необхідно видати клієнту";
            header("Location: ./books_list.php");
            exit;
        }
    }
    if(isset($_POST['return'])) {
        $provisionID = $_POST['return'];
        $bookID = $_POST['bookID'];
        $returnDate = date("Y-m-d");
        $query_create = $conn->prepare("UPDATE booksProvision SET ReturnDate=:returnDate WHERE ProvisionID=:provisionID;");
        $query_create->bindValue(':returnDate', $returnDate, SQLITE3_TEXT);
        $query_create->bindValue(':provisionID', $provisionID, SQLITE3_INTEGER);
        $query_create->execute();
        $query_create2 = $conn->prepare("UPDATE books SET Status='в наявності' WHERE BookID=:bookID;");
        $query_create2->bindValue(':bookID', $bookID, SQLITE3_INTEGER);
        $query_create2->execute();
        echo "Повернення зафіксовано";
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
    <?php
    if ($customer){
    ?>
    <div class="main-content librarian">
        <div class="col-lg-12">
            <div class="book-descript col-lg-9">
                <h1 class="author-name"><?php echo $customer['Surname'];?> <?php echo $customer['FirstName'];?> <?php echo $customer['ParentalName'];?> (ID = <?php echo $customer['СustomerID'];?>)</h1>
                <p><b>Адреса: </b><?php echo $customer['Address'];?></p>
                <p><b>Номер телефону: </b><?php echo $customer['PhoneNumber'];?></p>
                <p><b>Місце роботи: </b><?php echo $customer['Employment'];?></p>
            </div>
            <div class="buttons right col-lg-3">
                <form method="POST">
                    <input class="delete" type="submit" name="delete" value="видалити користувача"><br>
                </form>
            </div>
        </div>
        <div class="table col-lg-12">
        <h2>Видані книги:</h2>
        <table class="result-table col-lg-12">
            <tr>
                <th>ID</th>
                <th>Книга</th>
                <th>Бібліотекар</th>
                <th>Дата видачі</th>
                <th>Зафіксувати повернення</th>
            </tr>
            <?php while ($row = $result2->fetchArray(SQLITE3_ASSOC)) { ?>
            <tr>
                <td><?php echo $row['ProvisionID']; ?></td>
                <td><a href="http://localhost/LibraVerse/pages/book_profile.php?BookID=<?php echo $row['BookID']; ?>"><?php echo $row['Title']; ?></a></td>
                <td><a href="http://localhost/LibraVerse/pages/librarian_profile.php?LibrarianID=<?php echo $row['LibrarianID']; ?>"><?php echo $row['FirstName']; ?> <?php echo $row['Surname']; ?></a></td>
                <td><?php echo $row['ReceiptDate']; ?></td>
                <td class="return">
                    <form method="POST">
                        <input type="hidden" name="bookID" value="<?php echo $row['BookID']; ?>">
                        <button class="return-button" type="submit" name="return" value="<?php echo $row['ProvisionID']; ?>">Книгу повернуто</button>
                    </form>
                </td>
            </tr>
            <?php  } ?>
        </table>
        <div class="line">
            <form method="POST">
                <input class="provide customer" type="submit" name="provide" value="Видати">   
            </form>
        </div>
        </div>
        <div class="table col-lg-12">
        <h2>Історія повернутих книг:</h2>
        <table class="result-table col-lg-12">
            <tr>
                <th>ID</th>
                <th>Книга</th>
                <th>Бібліотекар</th>
                <th>Дата видачі</th>
                <th>Дата повернення</th>
            </tr>
            <?php while ($row = $result3->fetchArray(SQLITE3_ASSOC)) { ?>
            <tr>
                <td><?php echo $row['ProvisionID']; ?></td>
                <td><a href="http://localhost/LibraVerse/pages/book_profile.php?BookID=<?php echo $row['BookID']; ?>"><?php echo $row['Title']; ?></a></td>
                <td><a href="http://localhost/LibraVerse/pages/librarian_profile.php?LibrarianID=<?php echo $row['LibrarianID']; ?>"><?php echo $row['FirstName']; ?> <?php echo $row['Surname']; ?></a></td>
                <td><?php echo $row['ReceiptDate']; ?></td>
                <td><?php echo $row['ReturnDate']; ?></td>                
            </tr>
            <?php  } ?>
        </table>
        </div>
    </div>
    <?php
    } else {  ?>
        <div class="main-content error-msg" id="main-content">
            <img src="../images/error.svg" alt="error icon">
            <div class="error-text">
                <h1>Помилка! Клієнта з таким ID не знайдено</h1>
                <p>Поверніться до <a href="http://localhost/LibraVerse/pages/customers_list.php">списку клієнтів</a></p>
            </div>
        </div> 
    <?php   
    }
    ?>
    
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
            <h1>Помилка! Не отримано ID клієнта</h1>
            <p>Поверніться до <a href="http://localhost/LibraVerse/pages/customers_list.php">списку клієнтів</a></p>
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