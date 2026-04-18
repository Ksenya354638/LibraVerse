<?php
    $db_file = "../library.db";
    $conn = new SQLite3($db_file);
    if (!$conn) {
        echo "<div class='validation-msg'>
                    <img src='../images/error.svg' alt='error icon'>
                    <h2 class='validation-text'>Помилка! Не вдалося під'єднатися до бази даних</h2>     
                </div>";
    };
    $query1 = "SELECT COUNT(*) AS BooksNumber FROM books;";
    $query2 = "SELECT COUNT(*) AS BooksNumber FROM books WHERE Status='в наявності';";
    $query3 = "SELECT COUNT(*) AS BooksNumber FROM books WHERE Status='на руках';";
    $query4 = "SELECT AVG(Price) AS BooksPrice FROM books;";
    $result1 = $conn->query($query1);
    $result2 = $conn->query($query2);
    $result3 = $conn->query($query3);
    $result4 = $conn->query($query4);
    $row1 =$result1->fetchArray(SQLITE3_ASSOC);
    $row2 =$result2->fetchArray(SQLITE3_ASSOC);
    $row3 =$result3->fetchArray(SQLITE3_ASSOC);
    $row4 =$result4->fetchArray(SQLITE3_ASSOC);
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
    <div class="col-lg-3 statistics books">
        <h2>Загальна кількість книг в системі</h2>
        <p class="number"><?php echo $row1['BooksNumber']; ?></p>
    </div>
        <div class="col-lg-3 statistics books">
            <h2>Кількість книг в наявності</h2>
            <p class="number"><?php echo $row2['BooksNumber']; ?></p>
        </div>
    <div class="col-lg-3 statistics books">
        <h2>Кількість книг на руках</h2>
        <p class="number"><?php echo $row3['BooksNumber']; ?></p>
    </div>
    <div class="col-lg-3 statistics books">
        <h2>Середня вартість книги</h2>
        <p class="number"><?php echo round($row4['BooksPrice']); ?>грн</p>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</body>
</html>