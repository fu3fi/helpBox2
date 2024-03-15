<?php

    require_once("./geneticSchedule.php");

    $result = [2,6,5,1,4,0,5,3,6,4,2,3,6,1,0,4,5,2,3,0,6,1];

    $monthData = [[3,2,2,3,3,3,2],[3,3,3,2,3,2,2],[2,2,3,2,2,2,3],[2,3,3,2,2,2,3],[2,2,2,2,2,2,3],[2,3,2,2,2,2,3],[2,2,3,3,3,2,2],[3,3,3,2,2,3,3],[3,2,2,2,2,3,2],[3,3,2,3,2,2,3],[3,2,2,2,3,3,3],[3,3,2,2,2,3,3],[2,3,3,2,2,3,2],[3,3,2,2,3,3,3],[2,3,3,3,3,2,3],[3,3,2,3,2,2,3],[3,3,3,2,2,2,3],[3,3,2,3,3,3,3],[3,3,3,3,2,2,3],[3,3,3,2,3,2,2],[3,3,3,2,2,2,2],[3,2,3,2,3,3,3]];


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