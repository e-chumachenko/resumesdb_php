<?php
session_start();
require_once "pdo.php";

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Для удаления резюме необходимо войти в свою учётную запись";
    header('Location: login.php');
    return;
}
if (!isset($_GET['resume_id'])) {
    $_SESSION['error'] = "Ошибка, попробуйте, пожалуйста, ещё раз";
    header('Location: index.php');
    return;
}
$resume_id = htmlentities($_GET['resume_id']);

#Проверяем есть ли запрашиваемое резюме в базе данных
$resume = $pdo->prepare('SELECT * FROM resumes WHERE resume_id = :rsm_id');
$resume->execute(array(':rsm_id' => $resume_id));
$row = $resume->fetch(PDO::FETCH_ASSOC);
if ($row === false) {
    $_SESSION['error'] = "Запрашиваемое вами резюме в базе данных отсутствует";
    header('Location: index.php') ;
    return;
}
#Проверяем принадлежит ли запрашиваемое резюме пользователю
if ($_SESSION['user_id'] !== $row['user_id']) {
    $_SESSION['error'] = "Нет доступа. Вы можете удалять только собственные резюме";
    header('Location: index.php') ;
    return;
}

if (isset($_POST['cancel'])) {
    header('Location: index.php');
    return;
}
#Удаляем резюме
if (isset($_POST['delete']) && isset($_POST['resume_id'])) {
    $stmt = $pdo->prepare('DELETE FROM resumes WHERE resume_id = :rsm_id');
    $stmt->execute(array(':rsm_id' => $_POST['resume_id']));
    $_SESSION['success'] = "Ваше резюме было успешно удалено";
    header('Location: index.php');
    return;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Удаление резюме</title>
<script type="text/javascript">
//Повторно подтверждаем намерение пользователя удалить своё резюме
function doConfirm() {
    console.log('Confirming...');
    try {
        var del = confirm("Вы действительно хотите удалить своё резюме?"); 
        if (del == true){
            return true;
        } else {
            return false;
        }
    } catch(e) {
        return false;
    }
    return false;
}
</script>
</head>
<body>
<h1>Удаление резюме</h1>
<p>Имя: <?= htmlentities($row['first_name']) ?></p>
<p>Фамилия: <?= htmlentities($row['last_name']) ?></p>
<form method="POST">
<input type="hidden" name="resume_id" value="<?= $resume_id ?>">
<input type="submit" onclick="return doConfirm();" name="delete" value="Удалить">
<input type="submit" name="cancel" value="Отмена">
</form>
</body>
</html>
