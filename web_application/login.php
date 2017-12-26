<?php
session_start();
require_once "pdo.php";

if (isset($_POST['cancel'])) {
    header("Location: index.php");
    return;
}

#Пароли хранятся в базе данных с использованием соль-криптографии
$salt = 'XyZzy12*_';

if (isset($_POST['email']) && isset($_POST['pass'])) {
    # Удаляем из сессии данные предыдущего пользователя
    unset($_SESSION['user_id']);  
    unset($_SESSION['email']); 
    unset($_SESSION['first_name']);  
    unset($_SESSION['last_name']); 
    #Валидация данных пользователя
    if (strlen($_POST['email'])< 1 || strlen($_POST['pass'])< 1) {
        $_SESSION['error'] = "Необходимо заполнить все поля формы";
        header("Location: login.php");
        return;
    } elseif (strpos($_POST['email'], '@')=== false) {
        $_SESSION['error'] = "Неправильно указан адрес электронной почты";
        header("Location: login.php");
        return;    
    } else {
        #Проверяем наличие адреса электронной почты с таким пролем в базе данных
        $check = hash('md5', $salt.$_POST['pass']);
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = :em AND passwordh = :pw');
        $stmt->execute(array(':em' => $_POST['email'], ':pw' => $check));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        #Если пользователь существует, сохраняем его данные в сессии
        if ($row !== false) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['email'] = $_POST['email'];
            #Проверяем, есть ли в базе данных имя и фамилия пользователя
            $names = $pdo->prepare('SELECT first_name, last_name FROM resumes WHERE user_id = :uid');
            $names->execute(array(':uid' => $row['user_id']));
            $name = $names->fetch(PDO::FETCH_ASSOC);
            #Если имя и фамилия пользователя в базе данных есть, то также сохраняем их в сессии
            if ($name !== false) {
                $_SESSION['first_name'] = $name['first_name'];
                $_SESSION['last_name'] = $name['last_name'];            
            }
            header("Location: index.php");
            return;
        } else {
            $_SESSION['error'] = "Неправильная комбинация адреса электронной почты и пароля";
            header("Location: login.php");
            return;
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Вход</title>
</head>
<body>
<h1>Вход в свою учётную запись</h1>
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
<label for="pw">Пароль&nbsp;&nbsp;&nbsp;</label>
<input type="password" name="pass" id="pw">
<p>Заполните, пожалуйста, все поля формы</p><br>
<input type="submit" name="log_in" value="Войти">
<input type="submit" name="cancel"value="Отмена">
</form>
</body>
</html>