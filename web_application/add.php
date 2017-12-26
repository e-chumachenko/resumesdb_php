<?php
session_start();
require_once "pdo.php";

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Для создания резюме необходимо войти в свою учётную запись или зарегистрироваться";
    header('Location: index.php');
    return;
}
if (isset($_POST['cancel'])) {
    header('Location: index.php');
    return;
}
#Валидация данных об образовании
if (isset($_POST['countedu'])) {
    for ($i=1; $i<=$_POST['countedu']; $i++) {
    if (!isset($_POST['edu_year'.$i]) || !isset($_POST['degree'.$i]) || !isset($_POST['school'.$i])) continue;
        if (strlen($_POST['edu_year'.$i])<1 || strlen($_POST['degree'.$i])<1 || strlen($_POST['school'.$i])<1) {
            $_SESSION['error'] = "Необходимо заполнить все поля об образовании";
            header('Location: add.php');
            return;
        }
        if (!is_numeric($_POST['edu_year'.$i])) {
            $_SESSION['error'] = "Год окончания обучения может содержать только цифры";
            header('Location: add.php');
            return;
        }
    }
}
#Валидация данных об опыте работы
if (isset($_POST['countjob'])) {
    for ($i=1; $i<=$_POST['countjob']; $i++) {
        if (!isset($_POST['year_s'.$i]) || !isset($_POST['year_f'.$i]) || !isset($_POST['desc'.$i])) continue;
        if (strlen($_POST['year_s'.$i])<1 || strlen($_POST['year_f'.$i])<1 || strlen($_POST['desc'.$i])<1) {
            $_SESSION['error'] = "Необходимо заполнить все поля об опыте работы";
            header('Location: add.php');
            return;
        }
        if (!is_numeric($_POST['year_s'.$i]) || !is_numeric($_POST['year_f'.$i])) {
            $_SESSION['error'] = "Годы начала и окончания работы могут содержать только цифры";
            header('Location: add.php');
            return;
        }
    }
}
#Валидация данных резюме
if (isset($_POST['first_name']) && isset($_POST['patronymic_name']) && isset($_POST['last_name']) && 
isset($_POST['job_title']) && isset($_POST['resume_cv'])) {
    if (strlen($_POST['first_name'])<1 || strlen($_POST['last_name'])<1 || 
    strlen($_POST['job_title'])<1 || strlen($_POST['resume_cv'])<1) {
        $_SESSION['error'] = "Необходимо заполнить все поля резюме, отмеченные *";
        header('Location: add.php');
        return;
    #Добавляем резюме в базу данных
    } else {
        $stmt = $pdo->prepare('INSERT INTO resumes
        (user_id, first_name, patronymic_name, last_name, job_title, resume_cv) 
        VALUES ( :uid, :fn, :pn, :ln, :jobt, :rcv)');
        $stmt->execute(array(
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':pn' => $_POST['patronymic_name'],
        ':ln' => $_POST['last_name'],
        ':jobt' => $_POST['job_title'],
        ':rcv' => $_POST['resume_cv']));
        $resume_id = $pdo->lastInsertId();
        
        if (isset($_POST['countedu'])) {
            for($i=1; $i<=$_POST['countedu']; $i++) {
                if (!isset($_POST['edu_year'.$i]) || !isset($_POST['degree'.$i]) || !isset($_POST['school'.$i])) continue;
                $year = $_POST['edu_year'.$i];
                $degree = $_POST['degree'.$i];
                $school = $_POST['school'.$i];
                $institut_id = false;
                
                #Проверяем наличие ВУЗа в базе данных
                $institution = $pdo->prepare('SELECT institution_id FROM institutions WHERE name = :iname');
                $institution->execute(array(':iname' => $school));
                $row = $institution->fetch(PDO::FETCH_ASSOC);
                if ($row !== false) $institut_id = $row['institution_id'];
                
                #Если ВУЗа не было в базе данных, добавляем его
                if ($institut_id === false) {
                    $institut = $pdo->prepare('INSERT INTO institutions (name) VALUES (:nm)');
                    $institut->execute(array(':nm' => $school));
                    $institut_id = $pdo->lastInsertId();
                }
                
                #Добавляем запись об образовании в базу данных
                $education = $pdo->prepare('INSERT INTO education (resume_id, institution_id, year, degree)
                VALUES ( :rid, :iid, :yr, :dgr)');
                $education->execute(array(
                ':rid' => $resume_id,
                ':iid' => $institut_id,
                ':yr' => $year,
                ':dgr' => $degree));
            }
        }

        if (isset($_POST['countjob'])) {
            for($i=1; $i<=$_POST['countjob']; $i++) {
                if (!isset($_POST['year_s'.$i]) || !isset($_POST['year_f'.$i]) || !isset($_POST['desc'.$i])) continue;
                $years = $_POST['year_s'.$i];
                $yearf = $_POST['year_f'.$i];
                $desc = $_POST['desc'.$i];
                #Добавляем запись об опыте работы в базу данных
                $job = $pdo->prepare('INSERT INTO jobs (resume_id, year_start, year_finish, description)
                VALUES ( :rid, :yrs, :yrf, :dsc)');
                $job->execute(array(
                    ':rid' => $resume_id,
                    ':yrs' => $years,
                    ':yrf' => $yearf,
                    ':dsc' => $desc));
            }
        }
        $_SESSION['success'] = "Ваше резюме успешно создано";
        header('Location: index.php');
        return;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Создание резюме</title>
<link rel="stylesheet" href="stl.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>
<?php
echo ('<h1>Создать резюме ');
if (isset($_SESSION['email']) || isset($_SESSION['last_name'])) {
    if (isset($_SESSION['last_name'])) {
        echo ('для '.htmlentities($_SESSION['first_name']).' '.htmlentities($_SESSION['last_name']));
    } else {
        echo ('для '.htmlentities($_SESSION['email']));
    }
}
echo ("</h1>\n");
#Флеш-сообщение $_SESSION['error']
if (isset($_SESSION['error'])) {
    echo('<p style="color: red;">'.$_SESSION['error']."</p>\n");
    unset($_SESSION['error']);
}
?>  
<form method="POST">
<p><label for="fn"><sup class="r">*</sup>&nbsp;Имя:</label>
<input type="text" name="first_name" id="fn" size="65"></p>
<p><label for="pn">&nbsp;&nbsp;&nbsp;Отчество:</label>
<input type="text" name="patronymic_name" id="pn" size="60"></p>
<p><label for="ln"><sup class="r">*</sup>&nbsp;Фамилия:</label>
<input type="text" name="last_name" id="ln" size="60"></p>
<p><label for="jt"><sup class="r">*</sup>&nbsp;Название должности:</label><br/>
<input type="text" name="job_title" id="jt" size="80"></p>
<p><label for="rcv"><sup class="r">*</sup>&nbsp;Компетенции:</label><br/>
<textarea rows="8" cols="82" name="resume_cv" id="rcv"></textarea></p>
<p>Заполните, пожалуйста, все поля резюме, отмеченные&nbsp;<sup class="r">*</sup></p>
<p><label for="addedu">Образование:</label> 
<input type="submit" id="addedu" value="+">
<div id="education_field">
</div>
</p>
<p><label for="addjob">Опыт работы:</label>
<input type="submit" id="addjob" value="+">
<div id="job_field">
</div>
</p>
<input type="submit" name="add" value="Создать">
<input type="submit" name="cancel" value="Отмена">
<input id="counte" type="hidden" name="countedu">
<input id="countj" type="hidden" name="countjob">
</form>
<script>
countEdu = 0;
countJob = 0;
$(document).ready(function(){
    window.console && console.log('Document ready called');
    /*При нажатии на "Образование: +" создаются 3 дополнительных поля формы, объединённые в <div id="educationi"> (i=1,2 и т.д.):
      1. "Год окончания обучения:" (id="yedui", name="edu_yeari"), 
      2. "ВУЗ:" (id="schli", name="schooli") 
      3. "Специальность:" (id="dgri", name="degreei") */
    $('#addedu').click(function(event){
        event.preventDefault();
        countEdu++;
        //При нажатии на "-" соответсвующий <div id="educationi"> удаляется
        window.console && console.log("Adding education "+countEdu);
        $('#education_field').append(
            '<div id="education'+countEdu+'">\
            <p><label for="yedu'+countEdu+'"><sup class="r">*</sup>&nbsp;Год окончания обучения:</label>\
            <input type="number" id="yedu'+countEdu+'" name="edu_year'+countEdu+'" value="">\
            <input type="button" value="-"\
                onclick="$(\'#education'+countEdu+'\').remove();return false;"></p>\
            <p><label for="schl'+countEdu+'"><sup class="r">*</sup>&nbsp;ВУЗ:</label>\
            <input type="text" id="schl'+countEdu+'" name="school'+countEdu+'" size="75" class="school" value=""></p>\
            <p><label for="dgr'+countEdu+'"><sup class="r">*</sup>&nbsp;Специальность:</label>\
            <input type="text" id="dgr'+countEdu+'" name="degree'+countEdu+'" size="64" value=""></p>\
            </div>');
        /*Добавлям к полю "ВУЗ" опцию ввода с автодополнением (autocomplete widget - https://jqueryui.com/autocomplete/),
          источник данных - создаваемый school.php массив $retval в формате json 
          с названиями ВУЗов, содержащими вводимую пользователем комбинацию букв*/
        $('.school').autocomplete({
            source: "school.php"
        });
        $('#counte').attr("value", countEdu);
    });
    /*При нажатии на "Опыт работы: +" создаются 3 дополнительных поля формы, объединённые в <div id="jobi"> (i=1,2 и т.д.):
      1. "Год начала работы:" (id="yjobsi", name="year_si"), 
      2. "Год окончания работы:" (id="yjobfi", name="year_fi"), 
      3. "Название компании и должностные обязанности:" (id="descri", name="desci") */
    $('#addjob').click(function(event){
        event.preventDefault();
        countJob++;
        //При нажатии на "-" соответсвующий <div id="jobi"> удаляется
        window.console && console.log("Adding job "+countJob);
        $('#job_field').append(
            '<div id="job'+countJob+'">\
            <p><label for="yjobs'+countJob+'"><sup class="r">*</sup>&nbsp;Год начала работы:</label>\
            <input type="number" id="yjobs'+countJob+'" name="year_s'+countJob+'" value="">\
            <input type="button" value="-"\
                onclick="$(\'#job'+countJob+'\').remove();return false;"></p>\
            <p><label for="yjobf'+countJob+'"><sup class="r">*</sup>&nbsp;Год окончания работы:</label>\
            <input type="number" id="yjobf'+countJob+'" name="year_f'+countJob+'" value=""></p>\
            <p><label for="descr'+countJob+'"><sup class="r">*</sup>&nbsp;Название компании и должностные обязанности:<br>\
            <textarea id="descr'+countJob+'" name="desc'+countJob+'" rows="8" cols="80"></textarea></p>\
            </div>');
        $('#countj').attr("value", countJob);
    });
});
</script>
</body>
</html>
