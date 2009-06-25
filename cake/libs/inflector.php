<?php
/**
 * Pluralize and singularize English words.
 *
 * Used by Cake's naming conventions throughout the framework.
 *
 * PHP Version 5.x
 *
 * CakePHP(tm) : Rapid Development Framework (http://www.cakephp.org)
 * Copyright 2005-2009, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright 2005-2009, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.libs
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Included libraries.
 *
 */
if (!class_exists('Object')) {
	require LIBS . 'object.php';
}
if (!class_exists('Set')) {
	require LIBS . 'set.php';
}
/**
 * Pluralize and singularize English words.
 *
 * Inflector pluralizes and singularizes English nouns.
 * Used by Cake's naming conventions throughout the framework.
 * Test with $i = new Inflector(); $i->test();
 *
 * @package       cake
 * @subpackage    cake.cake.libs
 * @link          http://book.cakephp.org/view/491/Inflector
 */
final class Inflector extends Object {
/**
 * Plural rules from inflections.php
 *
 * @var array
 * @access public
 * @link http://book.cakephp.org/view/47/Custom-Inflections
 */
	public static $pluralRules = array();
/**
 * Un-inflected plural rules from inflections.php
 *
 * @var array
 * @access public
 * @link http://book.cakephp.org/view/47/Custom-Inflections
 */
	public static $uninflectedPlural = array();
/**
 * Irregular plural rules from inflections.php
 *
 * @var array
 * @access public
 * @link http://book.cakephp.org/view/47/Custom-Inflections
 */
	public static $irregularPlural = array();
/**
 * Singular rules from inflections.php
 *
 * @var array
 * @access public
 * @link http://book.cakephp.org/view/47/Custom-Inflections
 */
	public static $singularRules = array();
/**
 * Un-inflectd singular rules from inflections.php
 *
 * @var array
 * @access public
 * @link http://book.cakephp.org/view/47/Custom-Inflections
 */
	public static $uninflectedSingular = array();
/**
 * Irregular singular rules from inflections.php
 *
 * @var array
 * @access public
 * @link http://book.cakephp.org/view/47/Custom-Inflections
 */
	public static $irregularSingular = array();
/**
 * Pluralized words.
 *
 * @var array
 * @access private
 */
	private static $__pluralized = array();
/**
 * List of pluralization rules in the form of pattern => replacement.
 *
 * @var array
 * @access private
 */
	private static $__pluralRules = array();
/**
 * Singularized words.
 *
 * @var array
 * @access private
 */
	private static $__singularized = array();
/**
 * List of singularization rules in the form of pattern => replacement.
 *
 * @var array
 * @access private
 */
	private static $__singularRules = array();
/**
 * Return $word in plural form.
 *
 * @param string $word Word in singular
 * @return string Word in plural
 * @access public
 * @static
 * @link http://book.cakephp.org/view/572/Class-methods
 */
	public static function pluralize($word) {
		if (!isset(self::$__pluralRules) || empty(self::$__pluralRules)) {
			self::__initPluralRules();
		}
		if (isset(self::$__pluralized[$word])) {
			return self::$__pluralized[$word];
		}
		extract(self::$__pluralRules);

		if (!isset($regexUninflected) || !isset($regexIrregular)) {
			$regexUninflected = __enclose(join( '|', $uninflected));
			$regexIrregular = __enclose(join( '|', array_keys($irregular)));
			self::$__pluralRules['regexUninflected'] = $regexUninflected;
			self::$__pluralRules['regexIrregular'] = $regexIrregular;
		}

		if (preg_match('/(.*)\\b(' . $regexIrregular . ')$/i', $word, $regs)) {
			self::$__pluralized[$word] = $regs[1] . substr($word, 0, 1) . substr($irregular[strtolower($regs[2])], 1);
			return self::$__pluralized[$word];
		}

		if (preg_match('/^(' . $regexUninflected . ')$/i', $word, $regs)) {
			self::$__pluralized[$word] = $word;
			return $word;
		}

		foreach ($pluralRules as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				self::$__pluralized[$word] = preg_replace($rule, $replacement, $word);
				return self::$__pluralized[$word];
			}
		}
	}
/**
 * Return $word in singular form.
 *
 * @param string $word Word in plural
 * @return string Word in singular
 * @access public
 * @static
 * @link http://book.cakephp.org/view/572/Class-methods
 */
	static function singularize($word) {
		if (!isset(self::$__singularRules) || empty(self::$__singularRules)) {
			self::__initSingularRules();
		}

		if (isset(self::$__singularized[$word])) {
			return self::$__singularized[$word];
		}
		extract(self::$__singularRules);

		if (!isset($regexUninflected) || !isset($regexIrregular)) {
			$regexUninflected = __enclose(join( '|', $uninflected));
			$regexIrregular = __enclose(join( '|', array_keys($irregular)));
			self::$__singularRules['regexUninflected'] = $regexUninflected;
			self::$__singularRules['regexIrregular'] = $regexIrregular;
		}

		if (preg_match('/(.*)\\b(' . $regexIrregular . ')$/i', $word, $regs)) {
			self::$__singularized[$word] = $regs[1] . substr($word, 0, 1) . substr($irregular[strtolower($regs[2])], 1);
			return self::$__singularized[$word];
		}

		if (preg_match('/^(' . $regexUninflected . ')$/i', $word, $regs)) {
			self::$__singularized[$word] = $word;
			return $word;
		}

		foreach ($singularRules as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				self::$__singularized[$word] = preg_replace($rule, $replacement, $word);
				return self::$__singularized[$word];
			}
		}
		self::$__singularized[$word] = $word;
		return $word;
	}
/**
 * Returns the given lower_case_and_underscored_word as a CamelCased word.
 *
 * @param string $lower_case_and_underscored_word Word to camelize
 * @return string Camelized word. LikeThis.
 * @access public
 * @static
 * @link http://book.cakephp.org/view/572/Class-methods
 */
	static function camelize($lowerCaseAndUnderscoredWord) {
		return str_replace(" ", "", ucwords(str_replace("_", " ", $lowerCaseAndUnderscoredWord)));
	}
/**
 * Returns the given camelCasedWord as an underscored_word.
 *
 * @param string $camelCasedWord Camel-cased word to be "underscorized"
 * @return string Underscore-syntaxed version of the $camelCasedWord
 * @access public
 * @static
 * @link http://book.cakephp.org/view/572/Class-methods
 */
	static function underscore($camelCasedWord) {
		return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camelCasedWord));
	}
/**
 * Returns the given underscored_word_group as a Human Readable Word Group.
 * (Underscores are replaced by spaces and capitalized following words.)
 *
 * @param string $lower_case_and_underscored_word String to be made more readable
 * @return string Human-readable string
 * @access public
 * @static
 * @link http://book.cakephp.org/view/572/Class-methods
 */
	static function humanize($lowerCaseAndUnderscoredWord) {
		return ucwords(str_replace("_", " ", $lowerCaseAndUnderscoredWord));
	}
/**
 * Returns corresponding table name for given model $className. ("people" for the model class "Person").
 *
 * @param string $className Name of class to get database table name for
 * @return string Name of the database table for given class
 * @access public
 * @static
 * @link http://book.cakephp.org/view/572/Class-methods
 */
	static function tableize($className) {
		return self::pluralize(self::underscore($className));
	}
/**
 * Returns Cake model class name ("Person" for the database table "people".) for given database table.
 *
 * @param string $tableName Name of database table to get class name for
 * @return string Class name
 * @access public
 * @static
 * @link http://book.cakephp.org/view/572/Class-methods
 */
	static function classify($tableName) {
		return self::camelize(self::singularize($tableName));
	}
/**
 * Returns camelBacked version of an underscored string.
 *
 * @param string $string
 * @return string in variable form
 * @access public
 * @static
 * @link http://book.cakephp.org/view/572/Class-methods
 */
	static function variable($string) {
		$string = self::camelize(self::underscore($string));
		$replace = strtolower(substr($string, 0, 1));
		return preg_replace('/\\w/', $replace, $string, 1);
	}
/**
 * Returns a string with all spaces converted to underscores (by default), accented
 * characters converted to non-accented characters, and non word characters removed.
 *
 * @param string $string
 * @param string $replacement
 * @return string
 * @access public
 * @static
 * @link http://book.cakephp.org/view/572/Class-methods
 */
	static function slug($string, $replacement = '_') {
		if (!class_exists('String')) {
			require LIBS . 'string.php';
		}
		$map = array(
			'/à|á|å|â/' => 'a',
			'/è|é|ê|ẽ|ë/' => 'e',
			'/ì|í|î/' => 'i',
			'/ò|ó|ô|ø/' => 'o',
			'/ù|ú|ů|û/' => 'u',
			'/ç/' => 'c',
			'/ñ/' => 'n',
			'/ä|æ/' => 'ae',
			'/ö/' => 'oe',
			'/ü/' => 'ue',
			'/Ä/' => 'Ae',
			'/Ü/' => 'Ue',
			'/Ö/' => 'Oe',
			'/ß/' => 'ss',
			'/[^\w\s]/' => ' ',
			'/\\s+/' => $replacement,
			String::insert('/^[:replacement]+|[:replacement]+$/', array('replacement' => preg_quote($replacement, '/'))) => '',
		);
		return preg_replace(array_keys($map), array_values($map), $string);
	}
/**
 * Initializes plural inflection rules.
 *
 * @return void
 * @access private
 */
	private static function __initPluralRules() {
		$corePluralRules = array(
			'/(s)tatus$/i' => '\1\2tatuses',
			'/(quiz)$/i' => '\1zes',
			'/^(ox)$/i' => '\1\2en',
			'/([m|l])ouse$/i' => '\1ice',
			'/(matr|vert|ind)(ix|ex)$/i'  => '\1ices',
			'/(x|ch|ss|sh)$/i' => '\1es',
			'/([^aeiouy]|qu)y$/i' => '\1ies',
			'/(hive)$/i' => '\1s',
			'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
			'/sis$/i' => 'ses',
			'/([ti])um$/i' => '\1a',
			'/(p)erson$/i' => '\1eople',
			'/(m)an$/i' => '\1en',
			'/(c)hild$/i' => '\1hildren',
			'/(buffal|tomat)o$/i' => '\1\2oes',
			'/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\1i',
			'/us$/' => 'uses',
			'/(alias)$/i' => '\1es',
			'/(ax|cris|test)is$/i' => '\1es',
			'/s$/' => 's',
			'/^$/' => '',
			'/$/' => 's');

		$coreUninflectedPlural = array(
			'.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', 'Amoyese',
			'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo', 'cantus', 'carp', 'chassis', 'clippers',
			'cod', 'coitus', 'Congoese', 'contretemps', 'corps', 'debris', 'diabetes', 'djinn', 'eland', 'elk',
			'equipment', 'Faroese', 'flounder', 'Foochowese', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
			'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings', 'jackanapes', 'Kiplingese',
			'Kongoese', 'Lucchese', 'mackerel', 'Maltese', 'media', 'mews', 'moose', 'mumps', 'Nankingese', 'news',
			'nexus', 'Niasese', 'Pekingese', 'People', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese', 'proceedings',
			'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese', 'scissors', 'sea[- ]bass', 'series', 'Shavese', 'shears',
			'siemens', 'species', 'swine', 'testes', 'trousers', 'trout', 'tuna', 'Vermontese', 'Wenchowese',
			'whiting', 'wildebeest', 'Yengeese');

		$coreIrregularPlural = array(
			'atlas' => 'atlases',
			'beef' => 'beefs',
			'brother' => 'brothers',
			'child' => 'children',
			'corpus' => 'corpuses',
			'cow' => 'cows',
			'ganglion' => 'ganglions',
			'genie' => 'genies',
			'genus' => 'genera',
			'graffito' => 'graffiti',
			'hoof' => 'hoofs',
			'loaf' => 'loaves',
			'man' => 'men',
			'money' => 'monies',
			'mongoose' => 'mongooses',
			'move' => 'moves',
			'mythos' => 'mythoi',
			'numen' => 'numina',
			'occiput' => 'occiputs',
			'octopus' => 'octopuses',
			'opus' => 'opuses',
			'ox' => 'oxen',
			'penis' => 'penises',
			'person' => 'people',
			'sex' => 'sexes',
			'soliloquy' => 'soliloquies',
			'testis' => 'testes',
			'trilby' => 'trilbys',
			'turf' => 'turfs');

		$__pluralRules = Set::pushDiff(self::$pluralRules, $corePluralRules);
		$uninflected = Set::pushDiff(self::$uninflectedPlural, $coreUninflectedPlural);
		$irregular = Set::pushDiff(self::$irregularPlural, $coreIrregularPlural);

		self::$__pluralRules = array('pluralRules' => $__pluralRules, 'uninflected' => $uninflected, 'irregular' => $irregular);
		self::$__pluralized = array();
	}
/**
 * Initializes singular inflection rules.
 *
 * @return void
 * @access protected
 */
	private static function __initSingularRules() {
		$coreSingularRules = array(
			'/(s)tatuses$/i' => '\1\2tatus',
			'/^(.*)(menu)s$/i' => '\1\2',
			'/(quiz)zes$/i' => '\\1',
			'/(matr)ices$/i' => '\1ix',
			'/(vert|ind)ices$/i' => '\1ex',
			'/^(ox)en/i' => '\1',
			'/(alias)(es)*$/i' => '\1',
			'/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
			'/([ftw]ax)es/' => '\1',
			'/(cris|ax|test)es$/i' => '\1is',
			'/(shoe)s$/i' => '\1',
			'/(o)es$/i' => '\1',
			'/ouses$/' => 'ouse',
			'/uses$/' => 'us',
			'/([m|l])ice$/i' => '\1ouse',
			'/(x|ch|ss|sh)es$/i' => '\1',
			'/(m)ovies$/i' => '\1\2ovie',
			'/(s)eries$/i' => '\1\2eries',
			'/([^aeiouy]|qu)ies$/i' => '\1y',
			'/([lr])ves$/i' => '\1f',
			'/(tive)s$/i' => '\1',
			'/(hive)s$/i' => '\1',
			'/(drive)s$/i' => '\1',
			'/([^fo])ves$/i' => '\1fe',
			'/(^analy)ses$/i' => '\1sis',
			'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
			'/([ti])a$/i' => '\1um',
			'/(p)eople$/i' => '\1\2erson',
			'/(m)en$/i' => '\1an',
			'/(c)hildren$/i' => '\1\2hild',
			'/(n)ews$/i' => '\1\2ews',
			'/^(.*us)$/' => '\\1',
			'/s$/i' => '');

		$coreUninflectedSingular = array(
			'.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', '.*ss', 'Amoyese',
			'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo', 'cantus', 'carp', 'chassis', 'clippers',
			'cod', 'coitus', 'Congoese', 'contretemps', 'corps', 'debris', 'diabetes', 'djinn', 'eland', 'elk',
			'equipment', 'Faroese', 'flounder', 'Foochowese', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
			'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings', 'jackanapes', 'Kiplingese',
			'Kongoese', 'Lucchese', 'mackerel', 'Maltese', 'media', 'mews', 'moose', 'mumps', 'Nankingese', 'news',
			'nexus', 'Niasese', 'Pekingese', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese', 'proceedings',
			'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese', 'scissors', 'sea[- ]bass', 'series', 'Shavese', 'shears',
			'siemens', 'species', 'swine', 'testes', 'trousers', 'trout', 'tuna', 'Vermontese', 'Wenchowese',
			'whiting', 'wildebeest', 'Yengeese');

		$coreIrregularSingular = array(
			'atlases' => 'atlas',
			'beefs' => 'beef',
			'brothers' => 'brother',
			'children' => 'child',
			'corpuses' => 'corpus',
			'cows' => 'cow',
			'ganglions' => 'ganglion',
			'genies' => 'genie',
			'genera' => 'genus',
			'graffiti' => 'graffito',
			'hoofs' => 'hoof',
			'loaves' => 'loaf',
			'men' => 'man',
			'monies' => 'money',
			'mongooses' => 'mongoose',
			'moves' => 'move',
			'mythoi' => 'mythos',
			'numina' => 'numen',
			'occiputs' => 'occiput',
			'octopuses' => 'octopus',
			'opuses' => 'opus',
			'oxen' => 'ox',
			'penises' => 'penis',
			'people' => 'person',
			'sexes' => 'sex',
			'soliloquies' => 'soliloquy',
			'testes' => 'testis',
			'trilbys' => 'trilby',
			'turfs' => 'turf');

		$__singularRules = Set::pushDiff(self::$singularRules, $coreSingularRules);
		$uninflected = Set::pushDiff(self::$uninflectedSingular, $coreUninflectedSingular);
		$irregular = Set::pushDiff(self::$irregularSingular, $coreIrregularSingular);

		self::$__singularRules = array('singularRules' => $__singularRules, 'uninflected' => $uninflected, 'irregular' => $irregular);
		self::$__singularized = array();
	}
}
/**
 * Enclose a string for preg matching.
 *
 * @param string $string String to enclose
 * @return string Enclosed string
 */
	function __enclose($string) {
		return '(?:' . $string . ')';
	}
	require(CONFIGS.'inflections.php');
?>