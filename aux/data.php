<?php

function readPostValue($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function serializeArray($array)
{
    $answer = "";

    foreach ($array as $item) {
        if (strlen($item) > 0) {
            $answer = $answer . $item . "|";
        }
    }

    return $answer;
}

function unserializeArray($data)
{
    $answer = [];
    $data = readPostValue($data);

    $parts = explode("|", $data);
    foreach ($parts as $part) {
        if (strlen($part) > 0) {
            $answer[] = $part;
        }
    }

    return $answer;
}

?>