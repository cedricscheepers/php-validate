<?php
	// http://www.phpro.org/tutorials/Filtering-Data-with-PHP.html
	
	function colorFilter($var){
		return ($var !== NULL && $var !== false && $var !== '');
	}
			
	class Validate {
		protected $valid;
		protected $data;
		protected $parameters;
		protected $arr = array();
		protected $logErrors = true;
		
		public function __construct(&$data, $parameters = null, $logErrors = true) {
			$this -> data = $data;
			$this -> parameters = (isset($parameters) ? $parameters : null);
			$this -> logErrors = $logErrors;
			
			$this -> validate();
		}
		
		public function addParameters($parameters) {
			$this -> parameters = array_merge($this -> parameters, $parameters);

			$this -> validate();
		}
		
		public function isValid() {
			return !!($this -> valid);
		}
		
		public function field($field, $value = null) {
			if (!isset($value)) {
				return (isset($this -> arr[strtolower($field)]) ? $this -> arr[strtolower($field)] : null);
			} else {
				$this -> arr[strtolower($field)] = $value;
				
				return true;
			}
		}
		
		private function validate() {
			if (!isset($this -> parameters)) {
				exit;
			}
			
			$this -> valid = true;
			
			// Looping through the rules
			foreach ($this -> parameters as $fields => $conditions) {
				
				$rules = explode('|', strtolower($conditions));
				$rules = array_filter($rules);					// Remove empty items
				
				$rename = null;

				// If field is a composition of fields
				if (strpos($fields, ',') === false) {
					$value = (isset($this -> data[$fields]) && !in_array($this -> data[$fields], array('', 'null')) ? $this -> data[$fields] : null);
				} else {
					$arr = explode(',', $fields);
					$size = sizeof($arr);
					
					$value = '';
					
					for ($counter = 0; $counter < $size; ++$counter) {
						$value .= (strlen($value) > 0 ? ',' : '').(isset($this -> data[$arr[$counter]]) ? $this -> data[$arr[$counter]] : '');
					}
					
					$value = (strlen($value) > 0 ? $value : null);
				}
				
				// If required, remove from array
				$required = in_array('required', $rules);

				if ($required) {
					$pos = array_search('required', $rules);
					array_splice($rules, $pos, 1);
				}
				
				$size = sizeof($rules);

				// If default value exists, then set and remove from array
				if (strpos(strtolower($conditions), 'default') !== false) {
					
					for ($counter = 0; $counter < $size; ++$counter) {
						
						if (strpos($rules[$counter], 'default') !== false) {
							$tmp = explode(',', $rules[$counter]);
							
							// Exception - if not set and boolean, then value is false
							$value = (isset($value) ? $value : (strpos(strtolower($conditions), 'boolean') === false ? $tmp[1] : false));
							
							array_splice($rules, $counter, 1);
							$counter = $size;
						}
					}
				}

				$size = sizeof($rules);

				for ($counter = 0; $counter < $size; ++$counter) {
					$tmp = explode(',', $rules[$counter]);
					
					$rule = $tmp[0];
					$parameter = (isset($tmp[1]) ? $tmp[1] : null);
					
					switch ($rule) {
						case 'boolean':
							$this -> filterBoolean($required, $value);
							
							break;
							
						case 'color':
							$this -> filterColor($required, $value);
							
							break;
						
						case 'date':
						case 'datetime':
						case 'timestamp':
							$this -> filterDateTime($required, $value, $rule === 'date');
							
							break;
							
						case 'domain':
							$this -> filterDomain($required, $value);
							
							break;

						case 'email':
							$this -> filterEmail($required, $value);
							
							break;
							
						case 'encrypt':
							$this -> encryptField($value);
							
							break;
							
						case 'exact':
							$this -> filterOperand($required, $value, $parameter, '=');
						
							break;
							
						case 'exact_len':
						case 'exact_length':
							$this -> filterLength($required, $value, $parameter, '=');
							
							break;
							
						case 'facebook':
							$this -> filterFacebookURL($required, $value);
						
							break;
							
						case 'float':
							$this -> filterFloat($required, $value);
							
							break;
							
						case 'gender':
						case 'int':
							$this -> filterInt($required, $value);
							
							break;
							
						case 'google':
							$this -> filterGoogleURL($required, $value);
						
							break;
							
						case 'html':
							$this -> filterHTML($required, $value);

							break;

						case 'ip':
							$this -> filterIP($required, $value);
							
							break;
							
						case 'json':
							$this -> filterJSON($required, $value);
							
							break;
							
						case 'linkedin':
							$this -> filterLinkedInURL($required, $value);
						
							break;
							
						case 'lower':
							$value = strtolower($value);
							
							break;
							
						case 'max':
							$this -> filterOperand($required, $value, $parameter, '<=');

							break;

						case 'max_len':
						case 'max_length':
							$this -> filterLength($required, $value, $parameter, '<=');

							break;

						case 'min':
							$this -> filterOperand($required, $value, $parameter, '>=');

							break;

						case 'min_len':
						case 'min_length':
							$this -> filterLength($required, $value, $parameter, '>=');

							break;

						case 'name':
							$this -> filterName($required, $value);

							break;
							
						case 'password':
							$this -> filterPassword($required, $value);

							break;
							
						case 'rename':
							$rename = $parameter;
							
							break;

						case 'rgb':
							$value = hex2rgb($value);

							break;
							
						case 'string':
						case 'tel':
						case 'telnr':
						case 'text':
							$this -> filterText($required, $value);

							break;
							
						case 'skype':
							$this -> filterSkypeURL($required, $value);
						
							break;
							
						case 'sql':
							$this -> filterSQL($required, $value);
							
							break;
							
						case 'time':
							$this -> filterTime($required, $value);
						
							break;
							
						case 'twitter':
							$this -> filterTwitterURL($required, $value);
						
							break;
							
						case 'url':
							$this -> filterURL($required, $value);

							break;
						
						case 'upper':
							$value = strtoupper($value);
							
							break;

						default:
					}
				}

				if ($required) {
					$this -> filterRequired($value);
				}
				
				// Add to data array (and rename if required)
				$this -> arr[isset($rename) ? $rename : strtolower($fields)] = $value;
				
				if (!$this -> valid && $this -> logErrors) {
					error_log('Rule failed at field '.(isset($rename) ? $rename : $fields).' rule '.$rule);
				}
			}
		}
		
		private function encryptField(&$val) {
			$val = crypt($val, '!nine8oo29FIVE!');
		}
		
		private function filterRequired(&$val) {
			$this -> valid = $this -> valid && isset($val) && strlen($val) > 0;
		}
		
		private function filterBoolean(&$required, &$val) {			
			$tmp = (isset($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN) : false);
			
			// Converting all Booleans to 0 and 1
			$val = ($tmp ? 1 : 0);			
			$this -> valid = $this -> valid && ($required ? in_array($val, array(0,1)) : true);
		}

		private function filterColor(&$required, &$val) {			
			if (strpos($val, ',') !== false) {
				$arr = explode(',', $val);
				$arr = array_filter($arr, 'colorFilter');
				
				$tmp = sizeof($arr) === 3 && (int)$arr[0] < 256 && (int)$arr[1] < 256 && (int)$arr[2] < 256;
			} else {
				$tmp = preg_match('/^([a-f0-9]{3}|[a-f0-9]{6})$/i', $val);
			}

			$this -> valid = $this -> valid && ($required ? $tmp !== false : true);
		}

		private function filterDateTime(&$required, &$val, $date = false) {
			$arr = explode(',', $val);
			$check = false;
			
			// Safety check - sometimes no "," in val, then explode using "-"
			$arr = (isset($arr[1]) ? $arr : explode('-', $val));

			// Check number of days in month
			
			if ((int)$arr[0] > 0 && (int)$arr[1] > 0 && (int)$arr[2] > 0) {
				$days = cal_days_in_month(CAL_GREGORIAN, (int)$arr[1], (int)$arr[0]);
				$arr[2] = ((int)$arr[2] > $days ? $days : $arr[2]);
				
				$check = true;
			}
			
			if ($check && checkdate($arr[1], $arr[2], $arr[0])) {
				$timestamp = $arr[0].'-'.((int)$arr[1] < 10 ? '0' : '').$arr[1].'-'.((int)$arr[2] < 10 ? '0' : '').$arr[2];
			} else {
				$timestamp = '0000-00-00';

				$this -> valid = $this -> valid && ($required ? false : true);
			}
			
			if (!$date) {
				$timestamp .= (isset($arr[3]) && isset($arr[4]) ? ' '.
						((int)$arr[3] < 10 ? '0' : '').(int)$arr[3].':'.
						((int)$arr[4] < 10 ? '0' : '').(int)$arr[4].':00' : ' 00:00:00');
			}
			
			$val = $timestamp;
		}

		private function filterDomain(&$required, &$val) {
			$val = str_replace('@', '', $val);
			
			$tmp = preg_match('/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$/', $val);

			$this -> valid = $this -> valid && ($required ? $tmp !== false : true);
			
			$val = '@'.$val;
		}

		private function filterEmail(&$required, &$val) {
			$tmp = filter_var(filter_var(strtolower(trim($val)), FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
			
			$val = ($tmp !== false ? $tmp : $val);
			$this -> valid = $this -> valid && ($required ? $tmp !== false : true);
		}

		private function filterFacebookURL(&$required, &$val) {
			$tmp = preg_match('/^(http\:\/\/|https\:\/\/)?(?:www\.)?facebook\.com\/(?:(?:\w\.)*#!\/)?(?:pages\/)?(?:[\w\-\.]*\/)*([\w\-\.]*)/', $val);

			$this -> valid = $this -> valid && ($required ? $tmp !== false : true);
		}

		private function filterFloat(&$required, &$val) {
			$tmp = filter_var(filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT), FILTER_VALIDATE_FLOAT);

			$val = ($tmp !== false ? (float)$val : $val);
			$this -> valid = $this -> valid && ($required ? $tmp !== false : true);
		}

		private function filterGoogleURL(&$required, &$val) {
			$tmp = preg_match('/((http|https):\/\/)?(www[.])?plus\.google\.com\/.?\/?.?\/?([0-9]*)/', $val);

			$this -> valid = $this -> valid && ($required ? $tmp !== false : true);
		}

		private function filterHTML(&$required, &$val) {
			$val = trim(strip_tags($val, '<br><br/><p></p><a></a><b></b><ul></ul><ol></ol><li></li>'));
			
			$this -> valid = $this -> valid && ($required ? $val !== false : true);
		}

		private function filterInt(&$required, &$val) {
			$tmp = filter_var(filter_var($val, FILTER_SANITIZE_NUMBER_INT), FILTER_VALIDATE_INT);

			$val = ($tmp !== false ? (int)$val : $val);
			$this -> valid = $this -> valid && ($required ? $tmp !== false : true);
		}

		private function filterIP(&$required, &$val) {
			$val = filter_var(trim($val), FILTER_VALIDATE_IP);

			$this -> valid = $this -> valid && ($required ? $val !== false : true);
		}

		private function filterJSON(&$required, &$val) {
			$val = stripcslashes(htmlspecialchars_decode($val));
			
			$tmp = is_string($val) && is_object(json_decode($val)) && (json_last_error() === JSON_ERROR_NONE) ? true : false;

			$this -> valid = $this -> valid && ($required ? $tmp !== false : true);
		}

		private function filterLength(&$required, &$val, &$parameter, $operand) {
			switch ($operand) {
				case '=':		// Exactly
					$this -> valid = $this -> valid && ($required ? strlen($val) === (int)$parameter : true);

					break;
				case '<=':		// Max
					$this -> valid = $this -> valid && ($required ? strlen($val) <= (int)$parameter : true);

					break;
				case '>=':		// Min
					$this -> valid = $this -> valid && ($required ? strlen($val) >= (int)$parameter : true);

					break;
			}
		}
		
		private function filterLinkedInURL(&$required, &$val) {
			$tmp = preg_match('/(ftp|http|https):\/\/?(?:www\.)?linkedin.com(\w+:{0,1}\w*@)?(\S+)(:([0-9])+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/', $val);

			$this -> valid = $this -> valid && ($required ? $tmp !== false : true);
		}

		private function filterOperand(&$required, &$val, &$parameter, $operand) {
			switch ($operand) {
				case '=':		// Exactly
					$this -> valid = $this -> valid && ($required ? (int)$val === (int)$parameter : true);

					break;
				case '<=':		// Max
					$this -> valid = $this -> valid && ($required ? (int)$val <= (int)$parameter : true);

					break;
				case '>=':		// Min
					$this -> valid = $this -> valid && ($required ? (int)$val >= (int)$parameter : true);

					break;
			}
		}

		private function filterName(&$required, &$val) {
			$val = filter_var($val, FILTER_SANITIZE_STRING);

			if ($val === strtoupper($val) || $val === strtolower($val)) {
				
				$val = ucwords(strtolower($val));
				
				$find = array('Van ', 'Der ', 'Den ', 'De ', 'Dos ', 'Du ', 'La ', 'Le ', 'Mcc', 'Mcd');
				$sub = array('van ', 'der ', 'den ', 'de ', 'dos ', 'du ', 'la ', 'le ', 'McC', 'McD');
				
				$val = str_replace($find, $sub, $val);
			}

			$this -> valid = $this -> valid && ($required ? $val !== false : true);
		}

		private function filterPassword(&$required, &$val) {
			$val = filter_var($val, FILTER_SANITIZE_STRING);

			$this -> valid = $this -> valid && ($required ? $val !== false : true);
		}

		private function filterSkypeURL(&$required, &$val) {
			$tmp = preg_match('/^[a-z][a-z0-9\.,\-_]{5,31}$/i', $val);

			$this -> valid = $this -> valid && ($required ? $tmp !== false : true);
		}

		private function filterSQL(&$required, &$val) {
			$val = trim(htmlspecialchars($val));
			
			$this -> valid = $this -> valid && ($required ? $val !== false : true);
		}

		private function filterText(&$required, &$val) {
			$val = (is_array($val) ? implode(',', $val) : $val);
			
			$val = filter_var(trim($val), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
			
			$this -> valid = $this -> valid && ($required ? $val !== false : true);
		}
		
		private function filterTime(&$required, &$val) {
			$arr = explode(',', $val);
			$check = false;
			
			$val = (isset($arr[0]) && isset($arr[1]) ? ((int)$arr[0] < 10 ? '0' : '').(int)$arr[0].':'.
					((int)$arr[1] < 10 ? '0' : '').(int)$arr[1].':00' : ' 00:00:00');
			
			$tmp = preg_match('/(2[0-3]|[01][0-9]):([0-5][0-9])/', $val);

			$this -> valid = $this -> valid && ($required ? $tmp !== false : true);
		}

		private function filterTwitterURL(&$required, &$val) {
			$tmp = preg_match('/^(\@)?[A-Za-z0-9_]+$/', $val);

			$this -> valid = $this -> valid && ($required ? $tmp !== false : true);
		}

		private function filterURL(&$required, &$val) {
			if (strpos($val, 'http://') === false && strpos($val, 'https://') === false) {
				$val = 'http://'.$val;
			}
			
			$val = filter_var(filter_var(trim($val), FILTER_SANITIZE_URL), FILTER_VALIDATE_URL);

			$this -> valid = $this -> valid && ($required ? $val !== false : true);
		}
	}
	
	function validateDateTime(&$year, &$month, &$day, $hour = null, $min = null) {
		if (checkdate((int)$month, (int)$day, (int)$year)) {
			$timestamp = $year.'-'.((int)$month < 10 ? '0' : '').$month.'-'.((int)$day < 10 ? '0' : '').$day.
					(isset($hour) && isset($min) ? ' '.((int)$hour < 10 ? '0' : '').$hour.':'.((int)$min < 10 ? '0' : '').$min.':00' : ' 00:00:00');
		} else {
			$timestamp = (isset($hour) ? '0000-00-00 00:00:00' : '0000-00-00');
		}
		
		return $timestamp;
	}
	
	/*
		Name			sanitize
		Description		Makes a string safe to be inserted into a database.  It escapes characters, removes "/", replaces special characters and removes tags 
		Input			$val TEXT
		Output			TEXT
	*/

	function sanitize($val) {
		return filter_var(trim($val), FILTER_SANITIZE_STRING);
	}

	/*
		Name			sanitizeEmail
		Description		Makes a email safe to be inserted into a database.  It escapes characters, removes "/", replaces special characters and removes tags 
		Input			$val TEXT
		Output			TEXT
	*/

	function sanitizeEmail($val) {
		return filter_var(filter_var(strtolower(trim($val)), FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
	}
?>