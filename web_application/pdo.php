<?php
$pdo = new PDO('mysql:host=localhost;port=3306;dbname=resumesdb; charset=utf8', 'portfolio', 'rdb');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>