<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8"/>

    <script>
        $(document).ready(function () {
            $(".main").height($(document).height());

            $(window).on('resize', function () {
                var max_height = Math.max($(document).height(), $(window).height(), document.documentElement.clientHeight);
                $(".main").height(max_height);
            }).resize();
        });
    </script>

    <link href="../css/main.css" rel="stylesheet"/>
    <title>Iris Examination</title>
</head>
<body>
<?php
$examDir = "../exams/";

$examiner = "";
$allExams = scandir($examDir);

$exams = [];
for ($i = 0; $i < count($allExams); $i++) {
    if (substr($allExams[$i], -3) == "csv") {
        $exams[] = $allExams[$i];
    }
}
?>

<div class="main fixed-width">
    <div class="outer-container">
        <div class="header-container lateral-alignment">
            <div class="project-name"><h1>Iris Examination</h1></div>
            <div class="logo-university"><img src="../imgs/nd_logo.jpg" alt="University of Notre Dame"/></div>
        </div>

        <div class="form-container lateral-alignment">
            <form method="post" action="exam.php">
                <div class="col1"><label>Examiner</label><br/>
                    <input type="text" name="examiner"/>
                </div>

                <div class="col2"><label>Exam<br/></label>
                    <select name="exam" size="1">
                        <?php
                        foreach ($exams as $exam) {
                            echo "<option>" . $exam . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col3">
                    <input type="submit" name="start" value="START"/>
                </div>
            </form>
        </div>

        <div class="footnote-container">Computer Vision Research Lab - TSHEPII Project</div>
    </div>
</div>
</body>
</html>

