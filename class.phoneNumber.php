<?php
/**
 * HOW TO :
 * $nb = new PhoneNumber('01 23 45 67 89'); // parse number ; can throw a PhoneNumberException so try to manage it
 * echo $nb->display($aFormat); // display your number in your favorite format
 */
class PhoneNumber {
	
	public static $ccc = array(
		'1', '7', '20', '27', '30', '31', '32', '33', '34', '36', '39', '40', '41', '43', '44', '45',
		'46', '47', '48', '49', '51', '52', '53', '54', '55', '56', '57', '58', '60', '61', '62', '63',
		'64', '65', '66', '81', '82', '84', '86', '90', '91', '92', '93', '94', '95', '98', '211', '212',
		'213', '216', '218', '220', '221', '222', '223', '224', '225', '226', '227', '228', '229', '230',
		'231', '232', '233', '234', '235', '236', '237', '238', '239', '240', '241', '242', '243', '244',
		'245', '246', '247', '248', '249', '250', '251', '252', '253', '254', '255', '256', '257', '258',
		'260', '261', '262', '262', '263', '264', '265', '266', '267', '268', '269', '290', '291', '297',
		'298', '299', '350', '351', '352', '353', '354', '355', '356', '357', '358', '359', '370', '371',
		'372', '373', '374', '375', '376', '377', '378', '379', '380', '381', '382', '385', '386', '387',
		'389', '420', '421', '423', '500', '501', '502', '503', '504', '505', '506', '507', '508', '509',
		'590', '590', '591', '592', '593', '594', '595', '596', '597', '598', '599', '599', '670', '672',
		'673', '674', '675', '676', '677', '678', '679', '680', '681', '682', '683', '685', '686', '687',
		'688', '689', '690', '691', '692', '850', '852', '853', '855', '856', '880', '881', '886', '960',
		'961', '962', '963', '964', '965', '966', '967', '968', '970', '971', '972', '973', '974', '975',
		'976', '977', '992', '993', '994', '995', '996', '998', '1242', '1246', '1264', '1268', '1284',
		'1340', '1345', '1441', '1473', '1649', '1664', '1670', '1671', '1684', '1721', '1758', '1767',
		'1784', '1868', '1869', '1876'
	);
	
	/**
	 * @var Array ('ccc'=>country calling code, 'number'=> phone number (without leading 0))
	 */
	private $number;
	
	public function __construct($number) {
		$this->number = self::normalize($number);
	}
	
	/**
	 * Automatic french display
	 */
	public function displayLocal($delimiter=' ') {
		return $this->display(str_replace(' ',$delimiter,'xx xx xx xx xx'));
	}
	
	/**
	 * Automcatic international display (indicator + leading 0 in parenthesis + couples of digits)
	 */
	public function displayInternational($delimiter=' ') {
		return $this->display('+i '.str_replace(' ',$delimiter,'(0)x xx xx xx xx'));
	}
	
	/**
	 * Display using custom pattern - enter x for numbers (including leading zeros) and i for region indicator
	 * @param String $pattern your desired pattern
	 * @return formatted number
	 */
	public function display($pattern='xx xx xx xx xx') {
		$number = $this->number['number'];
		if (strpos($pattern, 'i')===false) {
			$number = '0'.$number;
		}
		$output = '';
		$pattern = str_split($pattern);
		$number = str_split($number);
		$j = 0;
		for($i=0; $i < count($pattern) || $j<count($number) ; $i++ ) {
			if ($i<count($pattern) && $pattern[$i]==='i') {
				$output .= $this->number['ccc'];
			}	
			else if ( ($i>=count($pattern) || 'x'=== $pattern[$i]) && $j<count($number)) {
				$output .= $number[$j];
				$j++;
			}
			else if ($i < count($pattern) && 'x'!== $pattern[$i]) {
				$output .= $pattern[$i];
			}
		}
		return $output;
	}
	
	/**
	 * Parse a number into phone number, and throw an exception if not successful
	 * This function is unfortunately better in parsing french numbers, but it still works for most of other countries
	 * You can add custom settings in the switch section to adapt to other countries
	 * @param String $number any string containing numbers
	 * @return Array an array ['ccc'=>region, 'number'=>without leading zeros if any]
	 * @throws PhoneNumberException
	 */
	public static function normalize($number) {
		$number = preg_replace('#[^0-9]#','',''.$number);
		$norm = array('ccc'=>'');
		switch (true) {
			
			case (strlen($number)==10 && substr($number,0,1)=='0'):
				$number = substr($number,1);
			case (strlen($number)==9 && substr($number,0,1)!=='0'):	
				$norm['ccc'] = '33';
				break;
			default:
				$found = false;
				$number = preg_replace('#^[0]+#', '', $number);
				$found = false;
				for ($i=4; $i>0; $i--) {
					$ccc = substr($number,0,$i);
					if (in_array($ccc,self::$ccc)) {
						$found = true;
						break;
					}
				}
				if ($found) {
					$norm['ccc'] = $ccc;
					$number = preg_replace('#^'.$ccc.'0*#','',$number);
				}
				else {
					throw new PhoneNumberException($number,1);
				}
				break;
		}
		$norm['number'] = $number;
		return $norm;
	}
	
}

/**
 * An exception class you can custom, override or whatever
 */
class PhoneNumberException extends Exception {
		
	function __construct($number='unknown',$code = 0, $message = "", $previous = NULL) {
		if ($message==='') {
			$message = "Phone number `{$number}` cannot be processed";
		}
		parent::__construct($message,$code,$previous);
	}
	
}
