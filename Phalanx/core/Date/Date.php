<?php
	
	class Date {
		public static function RelativeTime_old($timestamp){
			if($timestamp == '0000-00-00 00:00:00')
				return '';
			
			if(! is_numeric($timestamp))
				$timestamp = strtotime($timestamp);
			
			$difference = time() - $timestamp;
			$periods = array("segundo", "minuto", "hora", "dia", "semana", "mês", "ano", "década");
			$periods_plural = array("segundos", "minutos", "horas", "dias", "semanas", "meses", "anos", "décadas");
			$lengths = array("60", "60", "24", "7", "4.35", "12", "10");
	
			if ($difference > 0) {
				$ending = "atrás";
			} else {
				$difference = -$difference;
				$ending = "daqui a";
			}
			
			for($j = 0; $difference >= $lengths[$j]; $j++){
				if($lengths[$j] == 0){
					return "há muito tempo";
					break;
				}
				$difference /= $lengths[$j];
			}
				
			
			$difference = round($difference);
			
			if ($difference == 1){
				$text = "há $difference $periods[$j]";	
			} else {
				$text = "há $difference $periods_plural[$j]";
			}
				
			return $text;
		}
		
		public static function RelativeTime($timestamp) {
			setlocale(LC_TIME, "portuguese");
			
			if ($timestamp == '0000-00-00 00:00:00')
				return '';
	
			if (!is_numeric($timestamp))
				$timestamp = strtotime($timestamp);
			
			// Get time difference and setup arrays
			$difference = time() - $timestamp;
			$periods = array("segundo", "minuto", "hora", "dia", "semana", "mese", "ano");
			$lengths = array("60", "60", "24", "7", "4.35", "12");
	
			// Past or present
			if ($difference >= 0) {
				$ending = " há";
			} else {
				$difference = -$difference;
				$ending = "to go";
			}
	
			// Figure out difference by looping while less than array length
			// and difference is larger than lengths.
			$arr_len = count($lengths);
			for ($j = 0; $j < $arr_len && $difference >= $lengths[$j]; $j++) {
				$difference /= $lengths[$j];
			}
	
			// Round up
			$difference = round($difference);
	
			// Make plural if needed
			if ($difference != 1) {
				$periods[$j] .= "s";
			}
	
			// Default format
			$text = "{$ending} $difference $periods[$j]";
	
			// over 24 hours
			if ($j > 2) {
				// future date over a day formate with year
				if ($ending == "to go") {
					if ($j == 3 && $difference == 1) {
						$text = "Amanhã a " . date("H:i", $timestamp);
					} else {
						$text = date("j", $timestamp) . " de " . self::TranslateMonths(date("n", $timestamp)) ." de ". date("Y, \a\\t H:i", $timestamp);
					}
					return $text;
				}
	
				if ($j == 3 && $difference == 1){
					$text = "Ontem, ás " . date("H:i", $timestamp);
				} else if ($j == 3){
					$text = self::TranslateWeekDays(date("l", $timestamp)) .", às ". date("H:i", $timestamp);
				} else if ($j < 6 && !($j == 5 && $difference == 12)){
					if(date("Y") != date("Y", $timestamp)) $addYear = " de " . date("Y", $timestamp);
					$text = date("j", $timestamp) . " de " . self::TranslateMonths(date("n", $timestamp)) ."{$addYear}, às ". date("H:i", $timestamp);
				} else{
					$text = date("j", $timestamp) . " de " . self::TranslateMonths(date("n", $timestamp)) ." de ". date("Y , H:i", $timestamp);
				}
			}
	
			return $text;
		}

		protected static function TranslateWeekDays($weekdayEN){
			$weekdays = array(
				'SUNDAY'	=>	'Domingo',
				'MONDAY'	=>	'Segunda-feira',
				'TUESDAY'	=>	'Terça-feira',
				'WEDNESDAY'	=>	'Quarta-feira',
				'THURSDAY'	=>	'Quinta-feira',
				'FRIDAY'	=>	'Sexta-feira',
				'SATURDAY'	=>	'Sábado'
			);
			
			return $weekdays[strtoupper($weekdayEN)];
		}
		
		protected static function TranslateMonths($m){
			$month = array (1 => "Janeiro", 2 => "Fevereiro", 3 => "Março", 4 => "Abril", 5 => "Maio", 6 => "Junho", 7 => "Julho", 8 => "Agosto", 9 => "Setembro", 10 => "Outubro", 11 => "Novembro", 12 => "Dezembro");
			return $month[$m];
		}
	
	}
