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
    if(isset($_GET['LibrarianID'])) {
    $librarianID= $_GET['LibrarianID'];
    $query_create1 = $conn->prepare("SELECT * FROM librarians WHERE LibrarianID=:librarianID;");
    $query_create1->bindValue(':librarianID', $librarianID, SQLITE3_INTEGER);
    $query_create1->execute();
    $result1 = $query_create1->execute();
    $librarian = $result1->fetchArray(SQLITE3_ASSOC);

    $query_create2 = $conn->prepare("SELECT booksProvision.ProvisionID, books.BookID, books.Title, booksProvision.СustomerID, customers.FirstName, customers.Surname, customers.PhoneNumber, booksProvision.ReceiptDate, booksProvision.ReturnDate FROM booksProvision JOIN books ON booksProvision.BookID=books.BookID JOIN customers ON booksProvision.СustomerID=customers.СustomerID JOIN librarians ON booksProvision.LibrarianID=librarians.LibrarianID WHERE librarians.LibrarianID=:librarianID");
    $query_create2->bindValue(':librarianID', $librarianID, SQLITE3_INTEGER);
    $result2 = $query_create2->execute();

    if(isset($_POST['delete'])) {
        $query_create = $conn->prepare("DELETE FROM librarians WHERE LibrarianID=:librarianID;");
        $query_create->bindValue(':librarianID', $librarianID, SQLITE3_INTEGER);
        $query_create->execute();
        echo "<div class='validation-msg done'>
                <img src='../images/done.svg' alt='error icon'>
                <h2 class='validation-text'>Працівника успішно видалено з каталогу</h2>     
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
    <?php
    if ($librarian){
    ?>
    <div class="main-content librarian">
        <div class="col-lg-12 profile">
            <div class="book-descript col-lg-9">
                <h1 class="author-name"><?php echo $librarian['Surname'];?> <?php echo $librarian['FirstName'];?> <?php echo $librarian['ParentalName'];?> (ID = <?php echo $librarian['LibrarianID'];?>)</h1>
                <p><b>Адреса: </b><?php echo $librarian['Address'];?></p>
                <p><b>Номер телефону: </b><?php echo $librarian['PhoneNumber'];?></p>
                <p><b>Дата народження: </b><?php echo $librarian['BirthDate'];?></p>
                <p><b>Дата прийняття на роботу: </b><?php echo $librarian['EmploymentDate'];?></p>
                <p><b>Посада: </b><?php echo $librarian['Position'];?></p>
            </div>
            <div class="buttons right col-lg-3">
                <form method="POST">
                    <input class="delete" type="submit" name="delete" value="видалити працівника"><br>
                </form>
            </div>
        </div>
        <div class="table col-lg-12">
            <h2>Видані працівником книги:</h2>
            <table class="result-table col-lg-12">
            <tr>
                <th>ID</th>
                <th>Книга</th>
                <th>Клієнт</th>
                <th>Дата видачі</th>
                <th>Дата повернення</th>
            </tr>
            <?php while ($row = $result2->fetchArray(SQLITE3_ASSOC)) { ?>
            <tr>
                <td><?php echo $row['ProvisionID']; ?></td>
                <td><a href="http://localhost/LibraVerse/pages/book_profile.php?BookID=<?php echo $row['BookID']; ?>"><?php echo $row['Title']; ?></a></td>
                <td><a href="http://localhost/LibraVerse/pages/customer_profile.php?CustomerID=<?php echo $row['СustomerID']; ?>"><?php echo $row['FirstName']; ?> <?php echo $row['Surname']; ?></a></td>
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
                <h1>Помилка! Працівника з таким ID не знайдено</h1>
                <p>Поверніться до <a href="http://localhost/LibraVerse/pages/librarians_list.php">списку працівників</a></p>
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
                <h1>Помилка! Не отримано ID працівника</h1>
                <p>Поверніться до <a href="http://localhost/LibraVerse/pages/librarians_list.php">списку працівників</a></p>
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