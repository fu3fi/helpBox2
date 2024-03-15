<?php

    require_once("./geneticSchedule.php");

    // $result = [
    //     0 => 0,
    //     1 => 6,
    //     2 => 5,
    //     3 => 1,
    //     4 => 2,
    //     5 => 3,
    //     6 => 0,
    //     7 => 6,
    //     8 => 1,
    //     9 => 5,
    //     10 => 3,
    //     11 => 0,
    //     12 => 2,
    //     13 => 1,
    //     14 => 4,
    //     15 => 6,
    //     16 => 3,
    //     17 => 4,
    //     18 => 5,
    //     19 => 6,
    //     20 => 2,
    //     21 => 4,
    // ];

    // $preferencesByResult = [
    //     0 => 3,
    //     1 => 2,
    //     2 => 2,
    //     3 => 2,
    //     4 => 2,
    //     5 => 2,
    //     6 => 2,
    //     7 => 2,
    //     8 => 2,
    //     9 => 2,
    //     10 => 2,
    //     11 => 2,
    //     12 => 2,
    //     13 => 2,
    //     14 => 2,
    //     15 => 2,
    //     16 => 2,
    //     17 => 2,
    //     18 => 2,
    //     19 => 2,
    //     20 => 2,
    //     21 => 2,
    // ];

    $result = [2,6,5,1,4,0,5,3,6,4,2,3,6,1,0,4,5,2,3,0,6,1];

    $monthData = [[3,2,2,3,3,3,2],[3,3,3,2,3,2,2],[2,2,3,2,2,2,3],[2,3,3,2,2,2,3],[2,2,2,2,2,2,3],[2,3,2,2,2,2,3],[2,2,3,3,3,2,2],[3,3,3,2,2,3,3],[3,2,2,2,2,3,2],[3,3,2,3,2,2,3],[3,2,2,2,3,3,3],[3,3,2,2,2,3,3],[2,3,3,2,2,3,2],[3,3,2,2,3,3,3],[2,3,3,3,3,2,3],[3,3,2,3,2,2,3],[3,3,3,2,2,2,3],[3,3,2,3,3,3,3],[3,3,3,3,2,2,3],[3,3,3,2,3,2,2],[3,3,3,2,2,2,2],[3,2,3,2,3,3,3]];

    // $resultDiff = array_diff_assoc($updateResult, $result);
    $resultWithPreferences = [];
    foreach ($result as $dayIndex => $employeeId) {
        $resultWithPreferences[] = [
            'employeeId' => $employeeId,
            'score' => $monthData[$dayIndex][$employeeId],
        ];
    }

    // print_r($resultWithPreferences);

    $employeesDB = ScheduleDB::getPointer('./schedule.db');


    // print_r($resultWithPreferences);
    foreach ($resultWithPreferences as $employeedInfo) {
        $employeesDB->setAmountOfEmployee("increase", $employeedInfo['employeeId']);
        $employeesDB->setScoreOfEmployee("increase", $employeedInfo['employeeId'], $employeedInfo['score']);
    }

?>