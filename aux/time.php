<?php

function getTimestamp()
{
    date_default_timezone_set("UTC");

    $snow = explode(" ", microtime());
    $nowUTCTick = ($snow[0] + $snow[1]) * 1000.0;

    $nowInt = round($nowUTCTick);
    $nowFrac = abs($nowUTCTick - round($nowUTCTick));

    $date = new DateTime();
    $date->setTimestamp($nowInt / 1000);

    $output = date_format($date, 'Y-m-d H:i:s') . ":" . sprintf("%03d", round($nowFrac * 1000));
    $output = str_replace(array(':', ' '), '-', $output);

    return $output;
}

?>