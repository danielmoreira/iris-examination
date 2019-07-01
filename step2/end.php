<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8"/>

    <script src="../js/jquery-1.9.1.min.js"></script>
    <script>
        $(document).ready(function () {
            $(".main").height($(document).height());
            $(window).on('resize', function () {
                var max_height = Math.max($(document).height(), $(window).height(), document.documentElement.clientHeight);
                $(".main").height(max_height);
            }).resize()
        });
    </script>

    <link href="../css/main.css" rel="stylesheet"/>
    <title>End of Examination</title>
</head>
<body>
<?php
include '../aux/time.php';
include '../aux/data.php';

$annotationsDir = "../annotations/";

$examiner = "";
$times = [];
$outputFilePath = "";

$examId = 0;
$examCSVLines = [];
$examCSVLineIds = [];

$annotJSON = "";
$newAnswer = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $examiner = readPostValue($_POST["examiner"]);

    $times = unserializeArray($_POST["times"]);
    $times[] = getTimestamp();

    $outputFilePath = readPostValue($_POST['output_filepath']);

    $examId = readPostValue($_POST['exam_id']);
    $examCSVLines = unserializeArray($_POST['csv_lines']);
    $examCSVLineIds = unserializeArray($_POST["csv_line_ids"]);

    $annotJSON = readPostValue($_POST['annot_json']);
    $newAnswer = readPostValue($_POST['new_answer']);

    $oldContent = $examCSVLines[$examCSVLineIds[$examId - 1]];
    file_put_contents($annotationsDir . $outputFilePath, substr($oldContent, 0, strlen($oldContent) - 2) . "," . $times[($examId - 1) * 2] . "," . $times[($examId - 1) * 2 + 1] . "," . $newAnswer . PHP_EOL, FILE_APPEND | LOCK_EX);

    $jsonFilePath = explode(",", $oldContent)[8];
    $jsonFile = fopen($annotationsDir . substr($jsonFilePath, 0, strlen($jsonFilePath) - 2), "w");
    fwrite($jsonFile, $annotJSON . PHP_EOL);
    fclose($jsonFile);
} else {
    exit();
}

?>

<div class="main fixed-width">
    <div class="outer-container">
        <div class="header-container lateral-alignment">
            <div class="project-name"><h1>Iris Examination</h1></div>
            <div class="logo-university"><img src="../imgs/nd_logo.jpg"/></div>
        </div>

        <div class="form-container lateral-alignment">
            <form method="post" action="index.php">
                <div class="endup-examination-text">
                    <h1>The examination is over.</h1>
                    <h2>Thank you, <?php echo $examiner; ?>.</h2>
                </div>

                <div class="div-button-home">
                    <input type="submit" name="home" value="HOME"/>
                </div>
            </form>
        </div>

        <div class="footnote-container">Computer Vision Research Lab - TSHEPII Project</div>
    </div>
</div>
</body>
</html>
