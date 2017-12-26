<?php
session_start();
require_once "pdo.php";

if (isset($_POST['cancel'])) {
    header("Location: index.php");
    return;
}

#Пароли хранятся в базе данных с использованием соль-криптографии
$salt = 'XyZzy12*_';

if (isset($_POST['email']) && isset($_POST['pass1']) && isset($_POST['pass2'])) {
    # Удаляем из сессии данные предыдущего пользователя
    unset($_SESSION['user_id']);  
    unset($_SESSION['email']); 
    unset($_SESSION['first_name']);  
    unset($_SESSION['last_name']); 
    #Валидация данных пользователя    
    if (strlen($_POST['email'])< 1 || strlen($_POST['pass1'])< 1 || strlen($_POST['pass2'])< 1) {
        $_SESSION['error'] = "Необходимо заполнить все поля формы";
        header("Location: registry.php");
        return;
    } elseif (strpos($_POST['email'], '@')=== false) {
        $_SESSION['error'] = "Неправильно указан адрес электронной почты";
        header("Location: registry.php");
        return;
    } elseif ($_POST['pass1'] !== $_POST['pass2']) {
        $_SESSION['error'] = "Пароли не совпадают";
        header("Location: registry.php");
        return;
    } else {
        #Проверяем наличие пользователя с такой электронной почтой в базе данных        
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = :em');
        $stmt->execute(array(':em' => $_POST['email']));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row !== false) {
            $_SESSION['error'] = "Ваша электронная почта уже есть в базе данных. Пожалуйста, войдите в свою учётную запись.";
            header("Location: login.php");
            return;
        } else {
            #Если пользователя нет в базе данных, добавляем его
            $check = hash('md5', $salt.$_POST['pass1']);
            $addacc = $pdo->prepare('INSERT INTO users (email, passwordh)
            VALUES (:eml, :pwh)');
            $addacc->execute(array(
                ':eml' => $_POST['email'],
                ':pwh' => $check));
            #Сохраняем данные пользователя в сессии
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['email'] = $_POST['email'];
            $_SESSION['success'] = "Ваша учётная запись успешно создана";
            header("Location: index.php");
            return;
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Регистрация</title>
</head>
<body>
<h1>Регистрация новой учётной записи</h1>
<?php
#Флеш-сообщение $_SESSION['error']
if (isset($_SESSION['error'])) {
    echo('<p style="color: red;">'.$_SESSION['error']."</p>\n");
    unset($_SESSION['error']);
}
?>
<form method="POST">
<label for="nam">Эл.почта</label>
<input type="text" name="email" id="nam"><br>
<label for="pw1">Пароль&nbsp;&nbsp;&nbsp;</label>
<input type="password" name="pass1" id="pw1"><br>
<label for="pw2">Пароль&nbsp;&nbsp;&nbsp;</label>
<input type="password" name="pass2" id="pw2">
<p>Заполните, пожалуйста, все поля формы</p><br>
<input type="submit" name="log_in" value="Зарегистрироваться">
<input type="submit" name="cancel"value="Отмена">
</form>
</body>
</html>