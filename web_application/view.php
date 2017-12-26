<?php
session_start();
require_once "pdo.php";
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Просмотр резюме</title>
</head>
<body>
<h1>Резюме</h1>

<?php
#Получаем запрашиваемое резюме из базы данных
$rsm = $pdo->prepare('SELECT * FROM users JOIN resumes ON resumes.user_id = users.user_id where resume_id = :rsm_id');
$rsm->execute(array(':rsm_id' => $_GET['resume_id']));
$row = $rsm->fetch(PDO::FETCH_ASSOC);
if ($row === false) {
    $_SESSION['error'] = "Запрашиваемое вами резюме в базе данных отсутствует";
    header( 'Location: index.php' ) ;
    return;
}
#Выводим на экран резюме
echo ('<p><b>Имя:</b> '.htmlentities($row['first_name'])."</p>\n");
echo ('<p><b>Отчество:</b> '.htmlentities($row['patronymic_name'])."</p>\n");
echo ('<p><b>Фамилия:</b> '.htmlentities($row['last_name'])."</p>\n");
echo ('<p><b>Эл.почта:</b> '.htmlentities($row['email'])."</p>\n");
echo ('<p><b>Название должности:</b> '.htmlentities($row['job_title'])."</p>\n");
echo ("<p><b>Компетенции:</b><br/>\n<pre>\n".htmlentities($row['resume_cv'])."\n</pre></p>\n");

#Выводим на экран данные об образовании в виде маркированного списка
echo ("<p><b>Образование:</b><br/><ul>\n");
$education = $pdo->prepare('SELECT education.year, education.degree, institutions.name
    FROM education JOIN institutions ON education.institution_id = institutions.institution_id
    WHERE resume_id = :rsm_id ORDER BY year');
$education->execute(array(':rsm_id' => $_GET['resume_id']));
while ($row_ed = $education->fetch(PDO::FETCH_ASSOC)) {
    echo ('<li>'.htmlentities($row_ed['year']).' '.htmlentities($row_ed['name']).' - '.htmlentities($row_ed['degree'])."</li>\n");
}

#Выводим на экран данные об опыте работы в виде маркированного списка
echo ("</ul></p>\n<p><b>Опыт работы:</b><br/><ul>\n");
$jobs = $pdo->prepare('SELECT * FROM jobs WHERE resume_id = :rsm_id ORDER BY year_finish');
$jobs->execute(array(':rsm_id' => $_GET['resume_id']));
while ($row_j = $jobs->fetch(PDO::FETCH_ASSOC) ) {
    echo ('<li>'.htmlentities($row_j['year_start']).' - '.htmlentities($row_j['year_finish']).
    ' '.htmlentities($row_j['description'])."</li>\n");
}
echo ("</ul></p>\n");
?>
<br/>
<p><a href="index.php">Назад</a></p>
</body>
</html>
