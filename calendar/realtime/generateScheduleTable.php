<?php

	require_once("./commonSqlite.php");
	require_once("./geneticSchedule.php");
	
	$employeesDB = ScheduleDB::getPointer('./schedule.db');
	$employeesDB->createTable('employees', 
		['columns' 
			=> [
				[
					"name" => "id",
					"type" => "INTEGER",
					"mods" => "PRIMARY KEY AUTOINCREMENT",
				],
				[
					"name" => "name",
					"type" => "TEXT",
					"mods" => "",
				],
				[
					"name" => "nickName",
					"type" => "TEXT",
					"mods" => "NOT NULL",
				],
				[
					"name" => "amount",
					"type" => "INTEGER",
					"mods" => "NOT NULL DEFAULT 0",
				],
				[
					"name" => "scores",
					"type" => "INTEGER",
					"mods" => "NOT NULL DEFAULT 0",
				],
			]
		]
	);

	$employeesDB->insert('employees', [0, 's0', 's0', 0, 0]);
	$employeesDB->insert('employees', [1, 's1', 's1', 0, 0]);
	$employeesDB->insert('employees', [2, 's2', 's2', 0, 0]);
	$employeesDB->insert('employees', [3, 's3', 's3', 0, 0]);
	$employeesDB->insert('employees', [4, 's4', 's4', 0, 0]);
	$employeesDB->insert('employees', [5, 's5', 's5', 0, 0]);
	$employeesDB->insert('employees', [6, 's6', 's6', 0, 0]);
	
	echo "Success"."\n";
?>