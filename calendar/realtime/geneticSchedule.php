<?php

	require_once("./commonSqlite.php");

	class geneticSchedule {
		private $coeffMap = [
			"scoresDispersion" => 1,
			"commonScores" => 1,
			"burdenOfBreak" => [
				1 => 10**(-9),
				2 => 10**(-2),
			],
		];

		private $numberOfEmploees;

		function __construct($numberOfDay, $employees, $amount, $numberOfGeneration, $monthData) {
			$this->numberOfEmploees = count($employees);
			$this->workersQueue = array_slice($this->getEmployeesQueue($employees, $numberOfDay), 0, $numberOfDay);
			$this->employees = $employees;
			$this->monthData = $monthData;
			$this->amount = $amount;
			$this->numberOfGeneration = $numberOfGeneration;
			$this->population = $this->createPopulation($this->amount, $this->workersQueue);
		}

		function __destruct() {}

		function getMonthData() {
			return $this->monthData;
		}

		static function randomFloat($min, $max) {
			return ($min + lcg_value() * (abs($max - $min)));
		}

		private function getBurdenOfBreakArrangement($arrangement, $employees, $coeffMap) {
			foreach($arrangement as $dayValue => $employeeID) {
				if ($employees[$employeeID]['counter'] != -1) {
					$period = $dayValue - $employees[$employeeID]['counter'];
					$coeff = (array_key_exists($period, $coeffMap["burdenOfBreak"]))? $coeffMap["burdenOfBreak"][$period]: 1;
					$employees[$employeeID]["burdenOfBreakValue"] *= ($period * $coeff);
					$employees[$employeeID]['counter'] = $dayValue;
				} else {
					$employees[$employeeID]['counter'] = $dayValue;
				}
			}

			return array_map(function($employee) {return 1/$employee["burdenOfBreakValue"];}, $employees);
		}

		public function getStartSchedule() {
			return $this->population;
		}

		static function generateMonthData($numberOfDays, $employee) {
			$ids = array_keys($employee);
			$schedule = [];
			for ($i = 0; $i < $numberOfDays; $i++) {
				$schedule[$i] = [];
				foreach ($ids as $id) {
					$schedule[$i][$id] = rand(2, 3);
				}
			}
			return $schedule;
		}

		private function getEmployeesQueue($employees, $numberOfDays) {
			$pq = new PriQueue();
			$workersQueue = [];
			$pq->setExtractFlags(SplPriorityQueue::EXTR_BOTH);

			foreach ($employees as $employeeId => $employee) {
				$pq->insert($employeeId, $employee['amount']);
			}

			for ($i = 0; $i < $numberOfDays; $i++) {
				$info = $pq->current();
				$pq->next();
				$pq->insert($info['data'], $info['priority']+1);
				$workersQueue[] = $info['data'];
			}
			return $workersQueue;
		}

		private function getFitForArrangement($arrangement, $employees, $monthData, $coeffMap) {
			$distribution = $this->getDistribution($arrangement, $employees, $monthData);
			$paramsMap = [
				"scoresDispersion" => $this->getDispersion($distribution['score']),
			];
			return round($this->minimization($coeffMap, $paramsMap, array_sum($distribution['score']), array_sum($this->getBurdenOfBreakArrangement($arrangement, $employees, $coeffMap))), 5);
		}

		private function minimization($coeffMap, $paramsMap, $commonSum, $burdenOfBreak) {
			$result = 0;
			foreach ($paramsMap as $paramName => $paramValue) {
				$result += $coeffMap[$paramName] * $paramValue;
			}

			return $result + $coeffMap['commonScores'] * $commonSum + $burdenOfBreak;
		}

		private function getDispersion($distribution) {
			return geneticSchedule::getAvg(array_map(function($value) {return $value ** 2;}, $distribution)) - geneticSchedule::getAvg($distribution) ** 2;
		}

		static function getAvg($distribution) {
			return array_sum($distribution) / count($distribution);
		}

		private function getDistribution($arrangement, $employees, $monthData) {
			foreach($arrangement as $index => $piece) {
				$employees[$piece]['score'] += $monthData[$index][$piece];
			}

			return [
				"score" => array_map(function($employee) {return $employee['score'];}, $employees),
			];
		}

		static function generateTestEmpoloyees($numberOfEmploees) {
			$empoloees = [];
			for ($i = 0; $i < $numberOfEmploees; $i++) {
				$empoloees[] = [
					"name" => "s".$i,
					"amount" => 0,
					"score" => 0,
					"counter" => -1,
					"burdenOfBreakValue" => 1,
				];
			}
			return $empoloees;
		}

		private function createPopulation($amount, $workersQueue) {
			$population = [];
			for ($i = 0; $i < $amount; $i++) {
				$population[] = $this->getShuffledArr($workersQueue);
			}
			return $population;
		}

		private function survivalProbability($population, $employees, $monthData, $coeffMap) {
			$survivalProbabilityArr = [];
			foreach ($population as $individual) {
				$survivalProbabilityArr[] = 1 / $this->getFitForArrangement($individual, $employees, $monthData, $coeffMap);
			}
			$sum = array_sum($survivalProbabilityArr);
			return array_map(function($value) use ($sum) { return $value/$sum;}, $survivalProbabilityArr);
		}

		private function getIndividual($survivalProbabilityArr) {
			$point = geneticSchedule::randomFloat(0, 1);
			$niceProbability = 0;
			foreach($survivalProbabilityArr as $index => $survivalProbability) {
				$niceProbability += $survivalProbability;
				if ($niceProbability > $point) {
					return $index;
				}
			}
		}

		private function cross($firstIndividual, $secondIndividual, $employees, $monthData, $coeffMap) {
			$commonGens = [];
			$differentGens = [];
			$firstChallenger = [];
			$secondChallenger = [];

			foreach ($firstIndividual as $index => $gene) {
				if ($secondIndividual[$index] != $gene) {
					$differentGens[] = $gene;
				} else {
					$commonGens[] = $gene;
				}
			}
			$differentGens = $this->getShuffledArr($differentGens);
			$commonGens = $this->getShuffledArr($commonGens);
			
			foreach ($firstIndividual as $index => $gene) {
				if ($secondIndividual[$index] != $gene) {
					$secondIndividual[$index] = array_pop($differentGens);
					$firstChallenger[$index] = $secondIndividual[$index];
					$secondChallenger[$index] = $gene;
				} else {
					$nextCommonGen = array_pop($commonGens);
					$firstChallenger[$index] = $nextCommonGen;
					$secondChallenger[$index] = $nextCommonGen;
				}
			}

			$survivor = ($this->getFitForArrangement($firstChallenger, $employees, $monthData, $coeffMap) < $this->getFitForArrangement($secondChallenger, $employees, $monthData, $coeffMap)) ? $firstChallenger: $secondChallenger;

			return [$secondIndividual, $survivor];
		}

		private function getShuffledArr($arr) {
			shuffle($arr);
			return $arr;
		}

		public function getLocalMin() {
			$localMinValue = INF;
			$localMinArrangement = [];
			$nextPopulation = [];

			for ($i = 0; $i < $this->numberOfGeneration; $i++) {
				$survivalProbabilityArr = $this->survivalProbability($this->population, $this->employees, $this->monthData, $this->coeffMap);
				for ($z = 0; $z < $this->amount/2; $z++) {
					
					$firstIndividual = $this->getIndividual($survivalProbabilityArr);
					$secondIndividual = $this->getIndividual($survivalProbabilityArr);

					while ($firstIndividual == $secondIndividual) {
						$secondIndividual = $this->getIndividual($survivalProbabilityArr);
					}
			
					$children = $this->cross($this->population[$firstIndividual], $this->population[$secondIndividual], $this->employees, $this->monthData, $this->coeffMap);
					$nextPopulation[] = $children[0];
					$nextPopulation[] = $children[1];
				}

				$this->population = $nextPopulation;
				$nextPopulation = [];	

				$minimizationValues = array_map(function($p) {return $this->getFitForArrangement($p, $this->employees, $this->monthData, $this->coeffMap);}, $this->population);

				$minValue = min($minimizationValues);
				$key = array_keys($minimizationValues, $minValue);
				$minIndividual = $this->population[array_pop($key)];
				
				if ($minValue < $localMinValue) {
					$localMinValue = $minValue;
					$localMinArrangement = $minIndividual;
				}
			}
			$this->localMinArrangement = $localMinArrangement;
			$this->localMinValue = $localMinValue;

			return [
				"arr" => $localMinArrangement,
				"value" => $localMinValue,
			];
		}

		function getArrangementInfo() {
			return [
				"arr" => $this->localMinArrangement,
				"value" => $this->localMinValue,
			];
		}

		static function getPreferencesByArr($arr, $monthData) {
			return array_map(function($v1, $v2) {
				return $v2[$v1];
			}, $arr, $monthData);
		}

		function getNewInfoByEmployees($employees) {
			$result = $this->getArrangementInfo()['arr'];
			$resultOfPreferences = geneticSchedule::getPreferencesByArr($result, $this->getMonthData());
			foreach ($result as $dayIndex => $employeeId) {
				$employees[$employeeId]['amount'] += 1;
				$employees[$employeeId]['score'] += $resultOfPreferences[$dayIndex];
			}
			return $employees;
		}

		function getPreferencesByResult() {
			$result = $this->getArrangementInfo()['arr'];
			return geneticSchedule::getPreferencesByArr($result, $this->getMonthData());
		}
	};

	class PriQueue extends SplPriorityQueue {
		public function compare($first, $second) {
			if ($first == $second) {
				return 0;
			}
			return ($first < $second)? 1: -1;
		}
	};

	class ScheduleDB extends CommonSqlite {

		// function getEmployees() {
		// 	return static::$pointer->select("SELECT * FROM employees where ");
		// }

		function setAmountOfEmployee($mode, $employeeId) {
			$action = [
				"increase" => static::wrapper("CommonSqlite::increment"),
				"decrease" => static::wrapper("CommonSqlite::decrement"),
			][$mode];

			$amount = static::$classPointer->select("employees", $employeeId)[0]['amount'];
			static::$classPointer->update("employees", ['amount'], [$action($amount)], $employeeId);
		}

		function setScoreOfEmployee($mode, $employeeId, $step) {
			$action = [
				"increase" => static::wrapper("CommonSqlite::increase"),
				"decrease" => static::wrapper("CommonSqlite::decrease"),
			][$mode];

			$score = static::$classPointer->select("employees", $employeeId)[0]['scores'];
			static::$classPointer->update("employees", ['score'], [$action($score, $step)], $employeeId);
		}
	};
?>