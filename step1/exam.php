<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8"/>

    <script src="../js/jquery-1.9.1.min.js"></script>
    <script>
        $(document).ready(function () {
            window.onload = function () {
                var heigth_img = 640; //$("#img_iris_pair").height();
                var width_img = 1320; //$("#img_iris_pair").width();

                var ty = heigth_img + 15 + 40;
                var str_tranf = 'translate(-49%, ' + ty + 'px)';

                $(".rounded-container").height(heigth_img);
                $(".rounded-container").width(width_img + 20);

                $(".bottom-container").css({"transform": str_tranf});
                $(".bottom-container").width(width_img);

                $(".main").width(width_img + 100);
                $(".main").height($(document).height());
            };

            $(window).on('resize', function () {
                var max_height = Math.max($(document).height(), $(window).height(), document.documentElement.clientHeight);
                $(".main").height(max_height);
            }).resize();
        });
    </script>

    <link href="../css/main.css" rel="stylesheet"/>
    <title>Ongoing Examination - Opinion</title>
</head>
<body>
<?php
include '../aux/time.php';
include '../aux/data.php';

$examDir = "../exams/";
$irisDir = "../irises/";
$resultsDir = "../results/";

$examiner = "";
$times = [];
$outputFilePath = "";

$examId = 0;
$examCSVLines = [];
$examCSVLineIds = [];

$annotationFiles = [];
$lastAnswer = 3;
$iris = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $examiner = readPostValue($_POST["examiner"]);
    $outputFilePath = readPostValue($_POST['output_filepath']);

    if (strlen($outputFilePath) == 0) {
        # page loaded for the 1st time
        $examFile = readPostValue($_POST["exam"]);

        $outputFilePath = getTimestamp() . "_" . $examiner . ".csv";
        file_put_contents($resultsDir . $outputFilePath, "Iris1,Iris2,Answer,Difficulty,Webimage,Opinion,Begin,End,Annotation" . PHP_EOL, FILE_APPEND | LOCK_EX);

        $examId = 0;
        $examCSVLines = readCSVLines($examDir . $examFile);

        $i = 0;
        foreach ($examCSVLines as $_) {
            $examCSVLineIds[] = $i;
            $i = $i + 1;
        }
        shuffle($examCSVLineIds);
    } else {
        # page loaded for the 2nd or later time
        $times = unserializeArray($_POST["times"]);
        $times[] = getTimestamp();

        $examId = readPostValue($_POST['exam_id']);
        $examCSVLines = unserializeArray($_POST['csv_lines']);
        $examCSVLineIds = unserializeArray($_POST["csv_line_ids"]);

        $lastAnswer = readPostValue($_POST['answer']);

        $annotationFiles = unserializeArray($_POST["annotation_files"]);
        $annotationFiles[] = getTimestamp() . "_" . $examiner . "_" . $examId . ".json";

        $oldContent = $examCSVLines[$examCSVLineIds[$examId - 1]];
        file_put_contents($resultsDir . $outputFilePath, substr($oldContent, 0, strlen($oldContent) - 2) . "," . $lastAnswer . "," . $times[($examId - 1) * 2] . "," . $times[($examId - 1) * 2 + 1] . "," . $annotationFiles[$examId - 1] . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    $times[] = getTimestamp();
    $csvColumns = explode(",", $examCSVLines[$examCSVLineIds[$examId]]);
    $iris = $irisDir . $csvColumns[4];
} else {
    exit();
}

function readCSVLines($csvFilePath)
{
    $lines = [];

    $csvfile = fopen($csvFilePath, "r");
    fgets($csvfile); # header line is thrown away
    while (!feof($csvfile)) {
        $line = fgets($csvfile);
        if (strlen($line) > 0) {
            $lines[] = $line;
        }
    }
    fclose($csvfile);

    return $lines;
}

function nextPage($id, $idCount)
{
    if ($id < $idCount - 1) {
        echo "exam.php";
    } else {
        echo "end.php";
    }
}

function increaseId($id)
{
    echo $id + 1;
}

?>

<div class="main">
    <div class="outer-container">
        <div class="rounded-container">
            <div id="iris_pair" class="iris-container">
                <img id="img_iris_pair" src="<?php echo $iris; ?>" alt="iris pair"/>
            </div>
        </div>
    </div>

    <div class="bottom-container">
        <div class="bottom-container-inner canvas-off">
            <form method="post" action="<?php nextPage($examId, count($examCSVLines)); ?>">
                <div class="radio-list">
                    <ul>
                        <li>
                            <input type="radio" id="opt_1" name="answer" value="1"/>
                            <label for="opt_1">1. Same person (certain).</label>
                            <div class="check"></div>
                        </li>

                        <li>
                            <input type="radio" id="opt_2" name="answer" value="2"/>
                            <label for="opt_2">2. Same person (likely). </label>
                            <div class="check">
                                <div class="inside"></div>
                            </div>
                        </li>

                        <li>
                            <input type="radio" id="opt_3" name="answer" value="3" checked="checked"/>
                            <label for="opt_3"> 3. Uncertain. </label>
                            <div class="check">
                                <div class="inside"></div>
                            </div>
                        </li>

                        <li>
                            <input type="radio" id="opt_4" name="answer" value="4"/>
                            <label for="opt_4">4. Different people (likely). </label>
                            <div class="check">
                                <div class="inside"></div>
                            </div>
                        </li>

                        <li>
                            <input type="radio" id="opt_5" name="answer" value="5"/>
                            <label for="opt_5">5. Different people (certain). </label>
                            <div class="check">
                                <div class="inside"></div>
                            </div>
                        </li>
                    </ul>

                    <input type="hidden" name="examiner" value="<?php echo $examiner; ?>"/>
                    <input type="hidden" name="exam_id" value="<?php increaseId($examId); ?>"/>
                    <input type="hidden" name="csv_lines" value="<?php echo serializeArray($examCSVLines); ?>"/>
                    <input type="hidden" name="csv_line_ids" value="<?php echo serializeArray($examCSVLineIds); ?>"/>
                    <input type="hidden" name="times" value="<?php echo serializeArray($times); ?>"/>
                    <input type="hidden" name="output_filepath" value="<?php echo $outputFilePath; ?>"/>
                    <input type="hidden" name="annotation_files"
                           value="<?php echo serializeArray($annotationFiles); ?>"/>
                </div>

                <div class="button-next"><br/><br/><br/><br/>
                    <input class="next" type="submit" name="next" value="NEXT"/>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
