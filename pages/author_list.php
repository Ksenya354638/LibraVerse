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
    if(isset($_SESSION['LibrarianID'])){
    $query = "SELECT authors.AuthorID, authors.Name, authors.Surname, authors.BirthYear, COUNT(books.BookID) AS BookCount
    FROM authors JOIN books ON books.AuthorID = authors.AuthorID GROUP BY authors.AuthorID";
    if(isset($_POST['sort'])) {
        $sort_by = $_POST['sort'];
        if($sort_by === 'surname') {
        $query .= " ORDER BY authors.Surname;";
        } elseif ($sort_by === 'year') {
        $query .= " ORDER BY authors.BirthYear;";
        } elseif ($sort_by === 'book-count') {
            $query .= " ORDER BY BookCount;";
        }
    }
    $authors_result = $conn->query($query);
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
            <h1 class="text-center">Каталог авторів</h1>
        </div>
        <div>
            <div class="col-lg-3">
                <div class="side-menu">
                    <form action="" method="POST">
                        <h3 class="text-center">Сортувати за:</h3>
                        <button  type="submit" name="sort" value="surname">прізвищем</button><br>
                        <button type="submit" name="sort" value="year">роком народження</button><br>
                        <button type="submit" name="sort" value="book-count">кількістю творів</button>
                    </form>
                </div>
                <div class="button">
                    <a href="http://localhost/LibraVerse/pages/new_author.php" class="add">додати автора</a>
                </div>                
            </div>
            <div class="col-lg-9">
                <div class="table">
                    <table class="result-table col-lg-12">
                        <tr>
                            <th>ID</th>
                            <th>Ім’я</th>
                            <th>Прізвище</th>
                            <th>Рік народження</th>
                            <th>Кількість творів</th>
                        </tr>
                        <?php while ($row =$authors_result->fetchArray(SQLITE3_ASSOC)) { ?>
                        <tr>
                            <td><a href="http://localhost/LibraVerse/pages/author_profile.php?AuthorID=<?php echo $row['AuthorID']; ?>"><?php echo $row['AuthorID']; ?></a></td>
                            <td><a href="http://localhost/LibraVerse/pages/author_profile.php?AuthorID=<?php echo $row['AuthorID']; ?>"><?php echo $row['Name']; ?></a></td>
                            <td><a href="http://localhost/LibraVerse/pages/author_profile.php?AuthorID=<?php echo $row['AuthorID']; ?>"><?php echo $row['Surname']; ?></a></td>
                            <td><a href="http://localhost/LibraVerse/pages/author_profile.php?AuthorID=<?php echo $row['AuthorID']; ?>"><?php echo $row['BirthYear']; ?></a></td>
                            <td><a href="http://localhost/LibraVerse/pages/author_profile.php?AuthorID=<?php echo $row['AuthorID']; ?>"><?php echo $row['BookCount']; ?></a></td>
                        </tr>
                    <?php  } ?>
                    </table>
                </div>
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