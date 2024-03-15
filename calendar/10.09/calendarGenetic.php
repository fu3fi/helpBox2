<?php

	require_once("./geneticSchedule.php");
	require_once("./commonSqlite.php");

	$employeesDB = ScheduleDB::getPointer('./schedule.db');

	$results = $employeesDB->select('employees');
	if ($results == "") {
		echo "Error of empty result"."\n";
		exit(1);
	};
	

	$employees = [];
	foreach ($results as $index => $row) {
		$employees[$row['id']] = [
			"name" => $row['name'],
			"nickName" => $row['nickName'],
			"amount" => $row['amount'],
			"score" => $row['scores'],
			"counter" => -1,
			"burdenOfBreakValue" => 1,
		];
	}

	$monthData = geneticSchedule::generateMonthData(22, $employees);

	$numberOfDay = count($monthData);

	

	$amount = 500;
	$numberOfGeneration = 3000;
	$breakpoint = 300;

	$schedule = new geneticSchedule($numberOfDay, $employees, $amount, $numberOfGeneration, $monthData, $breakpoint);
	$result = $schedule->getLocalMin()['arr'];
	$employees = $schedule->getNewInfoByEmployees($employees);

	print_r(json_encode($result));
	print_r(json_encode($schedule->getPreferencesByResult()));

	// print_r(json_encode($monthData));


	// print_r($schedule->getPreferencesByResult());

	// for ($i = 0; $i < 30; $i++) {
		// $schedule = new geneticSchedule($numberOfDay, $employees, $amount, $numberOfGeneration, $monthData);
		// $result = $schedule->getLocalMin()['arr'];
		// $employees = $schedule->getNewInfoByEmployees($employees);
	// }
	
	// print_r($employees);

?>