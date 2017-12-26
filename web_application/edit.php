<?php
session_start();
require_once "pdo.php";

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Для редактирования резюме необходимо войти в свою учётную запись";
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
$rsmdata = $resume->fetch(PDO::FETCH_ASSOC);
if ($rsmdata === false) {
    $_SESSION['error'] = "Запрашиваемое вами резюме в базе данных отсутствует";
    header('Location: index.php') ;
    return;
}
#Проверяем принадлежит ли запрашиваемое резюме пользователю
if ($_SESSION['user_id'] !== $rsmdata['user_id']) {
    $_SESSION['error'] = "Нет доступа. Вы можете редактировать только собственные резюме";
    header('Location: index.php') ;
    return;
}

#Сохраняем данные об образовании в массив $educations
$ed = $pdo->prepare('SELECT * FROM education JOIN institutions ON education.institution_id = institutions.institution_id
    WHERE resume_id = :rsm_id ORDER BY year');
$ed->execute(array(':rsm_id' => $resume_id));
$educations = $ed->fetchAll(PDO::FETCH_ASSOC); 

#Сохраняем данные об опыте работы в массив $jobs
$jb = $pdo->prepare('SELECT * FROM jobs WHERE resume_id = :rsm_id ORDER BY year_finish');
$jb->execute(array(':rsm_id' => $resume_id));
$jobs = $jb->fetchAll(PDO::FETCH_ASSOC); 

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
            header("Location: edit.php?resume_id=".$_POST['resume_id']);
            return;
        }
        if (!is_numeric($_POST['edu_year'.$i])) {
            $_SESSION['error'] = "Год окончания обучения может содержать только цифры";
            header("Location: edit.php?resume_id=".$_POST['resume_id']);
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
            header("Location: edit.php?resume_id=".$_POST['resume_id']);
            return;
        }
        if (!is_numeric($_POST['year_s'.$i]) || !is_numeric($_POST['year_f'.$i])) {
            $_SESSION['error'] = "Годы начала и окончания работы могут содержать только цифры";
            header("Location: edit.php?resume_id=".$_POST['resume_id']);
            return;
        }
    }
}
#Валидация данных резюме
if (isset($_POST['first_name']) && isset($_POST['patronymic_name']) && isset($_POST['last_name']) && 
isset($_POST['job_title']) && isset($_POST['resume_cv']) && isset($_POST['resume_id'])) {
    if (strlen($_POST['first_name'])<1 || strlen($_POST['last_name'])<1 || 
    strlen($_POST['job_title'])<1 || strlen($_POST['resume_cv'])<1) {
        $_SESSION['error'] = "Необходимо заполнить все поля резюме, отмеченные *";
        header("Location: edit.php?resume_id=".$_POST['resume_id']);
        return;
    #Обновляем резюме в базе данных
    } else {
        $updatersm = $pdo->prepare('UPDATE resumes SET 
            user_id = :uid, first_name = :fn, patronymic_name = :pn, last_name = :ln, job_title = :jobt, resume_cv = :rcv 
            WHERE resume_id = :rsm_id');
        $updatersm->execute(array(
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':pn' => $_POST['patronymic_name'],
        ':ln' => $_POST['last_name'],
        ':jobt' => $_POST['job_title'],
        ':rcv' => $_POST['resume_cv'],
        ':rsm_id' => $_POST['resume_id'])); 
        
        if (isset($_POST['countedu'])) {
            #Удаляем все прежние записи об образовании
            $ed_old = $pdo->prepare('DELETE FROM education WHERE resume_id=:rid');
            $ed_old->execute(array( ':rid' => $_POST['resume_id']));
            #Добавляем обновлённые записи об образовании в базу данных
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
                ':rid' => $_POST['resume_id'],
                ':iid' => $institut_id,
                ':yr' => $year,
                ':dgr' => $degree));
            }
        }     
        
        if (isset($_POST['countjob'])) {
            #Удаляем все прежние записи об опыте работы
            $jb_old = $pdo->prepare('DELETE FROM jobs WHERE resume_id=:rsm');
            $jb_old->execute(array( ':rsm' => $_POST['resume_id']));
            #Добавляем обновлённые записи об опыте работы в базу данных
            for($i=1; $i<=$_POST['countjob']; $i++) {
                if (!isset($_POST['year_s'.$i]) || !isset($_POST['year_f'.$i]) || !isset($_POST['desc'.$i])) continue;
                $years = $_POST['year_s'.$i];
                $yearf = $_POST['year_f'.$i];
                $desc = $_POST['desc'.$i];
                #Добавляем запись об опыте работы в базу данных
                $job = $pdo->prepare('INSERT INTO jobs (resume_id, year_start, year_finish, description)
                VALUES ( :rid, :yrs, :yrf, :dsc)');
                $job->execute(array(
                    ':rid' => $_POST['resume_id'],
                    ':yrs' => $years,
                    ':yrf' => $yearf,
                    ':dsc' => $desc));
            }
        }
        $_SESSION['success'] = "Ваше резюме успешно обновлено";
        header('Location: index.php');
        return; 
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Редактирование резюме</title>
<link rel="stylesheet" href="stl.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>
<?php
echo ('<h1>Редактировать резюме ');
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
<input type="text" name="first_name" value="<?= htmlentities($rsmdata['first_name']); ?>" id="fn" size="65"></p>
<p><label for="pn">&nbsp;&nbsp;&nbsp;Отчество:</label>
<input type="text" name="patronymic_name" value="<?= htmlentities($rsmdata['patronymic_name']); ?>" id="pn" size="60"></p>
<p><label for="ln"><sup class="r">*</sup>&nbsp;Фамилия:</label>
<input type="text" name="last_name" value="<?= htmlentities($rsmdata['last_name']); ?>" id="ln" size="60"></p>
<p><label for="jt"><sup class="r">*</sup>&nbsp;Название должности:</label><br/>
<input type="text" name="job_title" value="<?= htmlentities($rsmdata['job_title']); ?>" id="jt" size="80"></p>
<p><label for="rcv"><sup class="r">*</sup>&nbsp;Компетенции:</label><br/>
<textarea rows="8" cols="82" name="resume_cv" id="rcv"><?= htmlentities($rsmdata['resume_cv']); ?></textarea></p>
<p>Заполните, пожалуйста, все поля резюме, отмеченные&nbsp;<sup class="r">*</sup></p>
<p><label for="addedu">Образование:</label> 
<input type="submit" id="addedu" value="+">
<div id="education_field">
<?php
#Выводим на экран поля формы, заполненные информацией об образовании пользователя из массива $educations 
#создание массива $educations - line 33
$edcount = 0;
if (count($educations)> 0) {
    foreach ($educations as $education) {
        $edcount++;
        //При нажатии на "-" соответсвующий <div id="educationi"> (i=1,2 и т.д.) удаляется
        echo ('<div id="education'.$edcount.'">'."\n");
        echo ('<p><label for="yedu'.$edcount.'"><sup class="r">*</sup>&nbsp;Год окончания обучения:</label>
            <input type="number" id="yedu'.$edcount.'" name="edu_year'.$edcount.'" value="'.htmlentities($education['year']).'">
            <input type="button" value="-"
                onclick="$(\'#education'.$edcount.'\').remove();return false;"></p>'."\n".
            '<p><label for="schl'.$edcount.'"><sup class="r">*</sup>&nbsp;ВУЗ:</label>
            <input type="text" id="schl'.$edcount.'" name="school'.$edcount.'" size="75" class="school" value="'.
            htmlentities($education['name']).'"></p>'."\n".
            '<p><label for="dgr'.$edcount.'"><sup class="r">*</sup>&nbsp;Специальность:</label>
            <input type="text" id="dgr'.$edcount.'" name="degree'.$edcount.'" size="64" value="'.htmlentities($education['degree']).'"></p>'
            ."\n</div>\n");
    }
}
?>
</div>
</p>
<p><label for="addjob">Опыт работы:</label> 
<input type="submit" id="addjob" value="+">
<div id="job_field">
<?php
#Выводим на экран поля формы, заполненные информацией об опыте работы пользователя из массива $jobs 
#создание массива $jobs - line 39
$jcount = 0;
if (count($jobs)> 0) {
    foreach ($jobs as $job) {
        $jcount++;
        //При нажатии на "-" соответсвующий <div id="jobi"> (i=1,2 и т.д.) удаляется
        echo ('<div id="job'.$jcount.'">'."\n");
        echo ('<p><label for="yjobs'.$jcount.'"><sup class="r">*</sup>&nbsp;Год начала работы:</label>
            <input type="number" id="yjobs'.$jcount.'" name="year_s'.$jcount.'" value="'.htmlentities($job['year_start']).'">
            <input type="button" value="-"
                onclick="$(\'#job'.$jcount.'\').remove();return false;"></p>'."\n".
            '<p><label for="yjobf'.$jcount.'"><sup class="r">*</sup>&nbsp;Год окончания работы:</label>
            <input type="number" id="yjobf'.$jcount.'" name="year_f'.$jcount.'" value="'.htmlentities($job['year_finish']).'">'."\n".
            '<p><label for="descr'.$jcount.'"><sup class="r">*</sup>&nbsp;Название компании и должностные обязанности:<br>
            <textarea id="descr'.$jcount.'" name="desc'.$jcount.'" rows="8" cols="80">'.htmlentities($job['description']).'</textarea></p>'
            ."\n</div>\n");
    }
}
?>
</div>
</p>
<input type="submit" name="add" value="Сохранить"> 
<input type="submit" name="cancel" value="Отмена">
<input type="hidden" name="resume_id" value="<?= $resume_id ?>">
<input id="counte" type="hidden" name="countedu">
<input id="countj" type="hidden" name="countjob">
</form>
<script>
//Сохраняем в переменной countEdu чило позиций об образовании, уже введённые пользователем
countEdu = '<?= $edcount ?>';
//Сохраняем в переменной countJob чило позиций об опыте работы, уже введённые пользователем
countJob = '<?= $jcount ?>';
$(document).ready(function(){
    window.console && console.log('Document ready called');
    /*Добавлям к к уже выведенным на экран полям "ВУЗ" опцию ввода с автодополнением (autocomplete widget - https://jqueryui.com/autocomplete/),
      источник данных - создаваемый school.php массив $retval в формате json 
      с названиями ВУЗов, содержащими вводимую пользователем комбинацию букв*/
    $('.school').autocomplete({
        source: "school.php"
    });
    $('#counte').attr("value", countEdu);
    $('#countj').attr("value", countJob);
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
            <input type="number" id="yjobf'+countJob+'" name="year_f'+countJob+'" value="">\
            <p><label for="descr'+countJob+'"><sup class="r">*</sup>&nbsp;Название компании и должностные обязанности:<br>\
            <textarea id="descr'+countJob+'" name="desc'+countJob+'" rows="8" cols="80"></textarea></p>\
            </div>');
        $('#countj').attr("value", countJob);
    });
});
</script>
</body>
</html>
