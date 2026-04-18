<?php
    $db_file = "../library.db";
    $conn = new SQLite3($db_file);
    if (!$conn) {
    echo "<div class='validation-msg'>
            <img src='../images/error.svg' alt='error icon'>
            <h2 class='validation-text'>Помилка! Не вдалося під'єднатися до бази даних</h2>     
        </div>";
    };
    $query1 = "SELECT COUNT(*) AS LibrarianNumber FROM librarians;";
    $query2 = "SELECT COUNT(*) AS LibrarianNumber FROM librarians WHERE Position='бібліотекар';";
    $query3 = "SELECT COUNT(*) AS LibrarianNumber FROM librarians WHERE Position='завідувач';";
    $result1 = $conn->query($query1);
    $result2 = $conn->query($query2);
    $result3 = $conn->query($query3);
    $row1 =$result1->fetchArray(SQLITE3_ASSOC);
    $row2 =$result2->fetchArray(SQLITE3_ASSOC);
    $row3 =$result3->fetchArray(SQLITE3_ASSOC);
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
    <div class="col-lg-4 statistics books">
        <h2>Загальна кількість працівників бібліотеки</h2>
        <p class="number"><?php echo $row1['LibrarianNumber']; ?></p>
    </div>
        <div class="col-lg-4 statistics books">
            <h2>Кількість бібліотекарів</h2>
            <p class="number"><?php echo $row2['LibrarianNumber']; ?></p>
        </div>
    <div class="col-lg-4 statistics books">
        <h2>Кількість завідувачів</h2>
        <p class="number"><?php echo $row3['LibrarianNumber']; ?></p>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</body>
</html>