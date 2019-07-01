<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8"/>

    <script src="../js/jquery-1.9.1.min.js"></script>
    <script src="../js/paper-core.js"></script>
    <script src="../js/paper-core.min.js"></script>
    <script src="../js/paper-full.js"></script>
    <script src="../js/paper-full.min.js"></script>
    <script src="../js/iris-annotator.js"></script>
    <script>
        $(document).ready(function () {
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

                $(window).on('resize', function () {
                    var max_height = Math.max($(document).height(), $(window).height(), document.documentElement.clientHeight);
                    $(".main").height(max_height);
                }).resize();

                // red button
                $("#non-match-link").click(function () {
                    $("#toggle").prop('checked', true);
                });

                // green button
                $("#match-link").click(function () {
                    $("#toggle").prop('checked', false);
                });

                // change answer link
                $("#change_link").click(function () {
                    document.getElementById('message_div').style.display = 'none';
                    document.getElementById('choose_div').style.display = 'block';

                    var max_height = Math.max($(document).height(), $(window).height(), document.documentElement.clientHeight);
                    $(".main").height(max_height);
                });
            }
        );
    </script>

    <link href="../css/main.css" rel="stylesheet"/>
    <link href="../css/jquery.contextMenu.css" rel="stylesheet"/>
    <title>Ongoing Examination - Annotation</title>
</head>
<body>
<?php
include '../aux/time.php';
include '../aux/data.php';

$resultsDir = "../results/";
$irisDir = "../irises/";
$annotationsDir = "../annotations/";

$examiner = "";
$times = [];
$outputFilePath = "";

$examId = 0;
$examCSVLines = [];
$examCSVLineIds = [];

$annotJSON = "";
$lastAnswer = 3;
$newAnswer = 3;
$iris = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $examiner = readPostValue($_POST["examiner"]);
    $outputFilePath = readPostValue($_POST['output_filepath']);

    if (strlen($outputFilePath) == 0) {
        # page loaded for the 1st time
        $examFile = readPostValue($_POST["exam"]);

        $outputFilePath = getTimestamp() . "_" . $examiner . ".csv";
        file_put_contents($annotationsDir . $outputFilePath, "Iris1,Iris2,Answer,Difficulty,Webimage,Opinion,Begin,End,Annotation,AnnotBegin,AnnotEnd,ReviewedOpinion" . PHP_EOL, FILE_APPEND | LOCK_EX);

        $examId = 0;
        $examCSVLines = readCSVLines($resultsDir . $examFile);
        $i = 0;
        foreach ($examCSVLines as $_) {
            $examCSVLineIds[] = $i;
            $i = $i + 1;
        }
        shuffle($examCSVLineIds);
    } else {
        # page loaded for the 2nd time
        $times = unserializeArray($_POST["times"]);
        $times[] = getTimestamp();

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
    }

    $times[] = getTimestamp();
    $csvColumns = explode(",", $examCSVLines[$examCSVLineIds[$examId]]);
    $lastAnswer = $csvColumns[5];
    $newAnswer = $lastAnswer;
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

function getAnswer($ans)
{
    switch ($ans) {
        case 1:
            echo "SAME PERSON (CERTAIN)";
            break;

        case 2:
            echo "SAME PERSON (LIKELY)";
            break;

        case 3:
            echo "UNCERTAIN";
            break;

        case 4:
            echo "DIFFERENT PEOPLE (LIKELY)";
            break;

        case 5:
            echo "DIFFERENT PEOPLE (CERTAIN)";
            break;

        default:
            break;
    }
}

function getChecked($lastAnswer, $id)
{
    if ($lastAnswer == $id)
        echo "checked";
}

?>

<div class="main">
    <div class="outer-container">
        <div class="drawing">
            <div class="tool-box-annotate">
                <a id="match-link" href="#">
                    <img class="annotate-button" src="../imgs/annotate_button_match.png"
                         alt="Annotate matching regions."/>
                </a>

                <label class="switch">
                    <input id="toggle" type="checkbox" name="annotype"/>
                    <div class="slider round"></div>
                </label>

                <a id="non-match-link" href="#">
                    <img class="annotate-button" src="../imgs/annotate_button_non_match.png"
                         alt="Annotate non-matching regions."/>
                </a>
            </div>
        </div>

        <div class="rounded-container">
            <div id="iris_pair" class="iris-container">
                <img id="img_iris_pair" src="<?php echo $iris; ?>" alt="iris pair"/>
            </div>

            <script type="text/javascript" src="../js/jquery.contextMenu.js"></script>
            <script type="text/javascript" src="../js/jquery.ui.position.js"></script>
            <script type="text/javascript" src="../js/iris-annotator.js"></script>
            <script type="text/javascript">var markup = $('#iris_pair').irisAnnotator();</script>
        </div>
    </div>

    <div class="bottom-container">
        <div class="bottom-container-inner canvas-on">
            <form method="post" action="<?php nextPage($examId, count($examCSVLines)); ?>">
                <div id="message_div" class="test-canvas">
                    <p class="text-decision">Your decision <a id="change_link" href="#">(change)</a></p>
                    <p class="text-answers"><?php getAnswer($lastAnswer); ?></p>
                    <p class="text-annotate">Please annotate 2-5 <span style="color: #38761d; font-weight:bolder">matching</span>
                        or <span style="color: #e06666; font-weight:bolder">non-matching</span> regions.</p>

                    <input type="hidden" name="examiner" value="<?php echo $examiner; ?>"/>
                    <input type="hidden" name="exam_id" value="<?php increaseId($examId); ?>"/>
                    <input type="hidden" name="csv_lines" value="<?php echo serializeArray($examCSVLines) ?>"/>
                    <input type="hidden" name="csv_line_ids" value="<?php echo serializeArray($examCSVLineIds) ?>"/>
                    <input type="hidden" name="times" value="<?php echo serializeArray($times); ?>"/>
                    <input type="hidden" name="output_filepath" value="<?php echo $outputFilePath; ?>"/>
                    <input type="hidden" id="annot_json_id" name="annot_json" value="<?php echo $annotJSON; ?>"/>
                </div>

                <div id="choose_div" class="radio-list" style="display: none">
                    <ul>
                        <li>
                            <input type="radio" id="opt_1" name="new_answer"
                                   value="1" <?php getChecked($lastAnswer, 1) ?>/>
                            <label for="opt_1">1. Same person (certain).</label>
                            <div class="check"></div>
                        </li>

                        <li>
                            <input type="radio" id="opt_2" name="new_answer"
                                   value="2" <?php getChecked($lastAnswer, 2) ?>/>
                            <label for="opt_2">2. Same person (likely). </label>
                            <div class="check">
                                <div class="inside"></div>
                            </div>
                        </li>

                        <li>
                            <input type="radio" id="opt_3" name="new_answer"
                                   value="3" <?php getChecked($lastAnswer, 3) ?> />
                            <label for="opt_3"> 3. Uncertain. </label>
                            <div class="check">
                                <div class="inside"></div>
                            </div>
                        </li>

                        <li>
                            <input type="radio" id="opt_4" name="new_answer"
                                   value="4" <?php getChecked($lastAnswer, 4) ?>/>
                            <label for="opt_4">4. Different people (likely). </label>
                            <div class="check">
                                <div class="inside"></div>
                            </div>
                        </li>

                        <li>
                            <input type="radio" id="opt_5" name="new_answer"
                                   value="5" <?php getChecked($lastAnswer, 5) ?>/>
                            <label for="opt_5">5. Different people (certain). </label>
                            <div class="check">
                                <div class="inside"></div>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="button-next"><br/><br/>
                    <input class="next" type="submit" name="next" value="NEXT"/>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
