<?php
session_start();
require_once "pdo.php";
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Резюме</title>
<link rel="stylesheet" href="stl.css">
</head>
<body>
<?php
#Предусматриваем два набора стилей для ситуаций до и после верификации пользователя
if (isset($_SESSION['user_id'])){
    echo ("<div>\n");
} else {
    echo ('<div id="lside">'."\n");
}
echo ('<h1 id="c">База данных резюме</h1><br>'."\n");

#Флеш-сообщения $_SESSION['success'] и $_SESSION['error']
if (isset($_SESSION['success'])) {
    echo('<p style="color: green;">'.$_SESSION['success']."</p>\n");
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo('<p style="color: red;">'.$_SESSION['error']."</p>\n");
    unset($_SESSION['error']);
}

#Добавляем форму для поисковых запросов по базе данных резюме
echo ('<form method="GET">');
echo ('<input type="text" name="search" placeholder="Поиск">');
echo ('<input type="submit" name="find" value="Найти">');
echo ('</form>');

# Проверяем наличие записей в базе данных резюме
$content = $pdo->query('SELECT * FROM resumes LIMIT 1');
$row_c = $content->fetch(PDO::FETCH_ASSOC);
if ($row_c === false) {
        echo ("<h3>В базе данных нет резюме</h3>\n");
} else {
    echo("<p><table>\n<tr><th>ФИО</th><th>Название должности</th></tr>\n");
}

#Если есть поисковый запрос - выводим на экран его результаты
if (isset($_GET['search'])) {
    $stmt = $pdo->prepare('SELECT first_name, patronymic_name, last_name, job_title, user_id, resume_id FROM resumes 
    WHERE first_name LIKE :srch OR patronymic_name LIKE :srch OR last_name LIKE :srch OR job_title LIKE :srch');
    $stmt->execute(array(':srch' => '%'.$_GET['search'].'%'));
} else {$stmt = $pdo->query('SELECT first_name, patronymic_name, last_name, job_title, user_id, resume_id FROM resumes');
}
/* Выводим резюме на экран в виде таблицы в двух вариантах:
   1. после верификации пользователя c возможностью создавать, редактировать и удалять свое резюме,
   2. до верификации пользователя без возможности создавать, редактировать и удалять свое резюме */
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo('<tr><td>'.htmlentities($row['last_name']).' '.htmlentities($row['first_name']).' '.
    htmlentities($row['patronymic_name']).'</a></td>');
    echo('<td><a href="view.php?resume_id='.$row['resume_id'].'">'.htmlentities($row['job_title']).'</td>');
    if (isset($_SESSION['user_id']) && ($_SESSION['user_id']==$row['user_id'])) {
        echo('<td id="ed"><a href="edit.php?resume_id='.$row['resume_id'].'">Редактировать</a> / '.
        '<a href="delete.php?resume_id='.$row['resume_id'].'">Удалить</a>'."</td></tr>\n");
    } else {
        echo("</tr>\n");
    }
}
if ($row_c !== false) {
    echo("</table></p>\n");
}   
if (isset($_SESSION['user_id'])) {
    echo ('<p><a href="add.php">Создать новое резюме</a></p>'."\n");
    echo ('<p><a href="logout.php">Выйти</a></p>'."\n");
}
echo ("</div>\n"); 

#<div id="rside"> с ссылками Войти и Зарегистрироваться показывается только неверифицированным пользователям
if (!isset($_SESSION['user_id'])) {
    echo ('<div id="rside">'."\n");
    echo ('<h3>Чтобы создать или отредактировать своё резюме, пожалуйста, войдите или зарегистрируйтесь</h3>'."\n");
    echo ('<p class="lgnp"><a href="login.php">Войти</a></p>'."\n");
    echo ('<p class="lgnp"><a href="registry.php">Зарегистрироваться</a></p>'."\n");
    echo ("</div>\n");
}
?>
</body>
</html>