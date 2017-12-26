<?php
session_start();
require_once "pdo.php";
#Создаём массив $retval в формате json с названиями ВУЗов, содержащими вводимую пользователем комбинацию букв
if (isset($_REQUEST['term'])) {
    $stmt = $pdo->prepare('SELECT name FROM institutions WHERE name LIKE :prefix');
    $stmt->execute(array( ':prefix' => '%'.$_REQUEST['term'].'%'));
    $retval = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $retval[] = htmlentities($row['name']);
    }
    echo(json_encode($retval, JSON_PRETTY_PRINT));
}
?>