<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ORM Base Class
 *
 * @author     AirPOS Ltd.
 * @copyright  (c) 2010 AirPOS Ltd.
 */
abstract class ORM extends Kohana_ORM
{
	/**
	 * Constructor
	 */
	public function __construct($id = NULL)
	{
		// Set Database to appliction's environment
		$this->_db = Kohana::$environment;

		parent::__construct($id);
	}
	
	/**
	 * Start Transaction
	 */
	public static function start()
	{
		DB::query(NULL, "START TRANSACTION")->execute(Kohana::$environment);
	}
	
	/**
	 * Commit Transaction
	 */
	public static function commit()
	{
		DB::query(NULL, "COMMIT")->execute(Kohana::$environment);
	}
	
	/**
	 * Rollback Transaction
	 */
	public static function rollback()
	{
		DB::query(NULL, "ROLLBACK")->execute(Kohana::$environment);
	}
	
	/**
	 * Validation Errors
	 */
	public function errors($file = 'validate', $translate = TRUE)
	{
		if (is_object($this->_validate))
			return $this->_validate->errors($file, $translate);
		
		return array();
	}
	
	/**
	 * Values
	 */
	public function values($values = array())
	{
		if (empty($values))
			return $this->_object;
		
		return parent::values($values);
	}
	
	/**
	 * Add Validation Rule
	 */
	public function add_rule(array $rule)
	{
		$this->_rules += $rule;
	}
	
	/**
	 * Load Result
	 *
	 * Override ORM _load_result method so a UUID can be set as the primary_key
	 * so we know what it is before the object has even been saved.
	 */
	protected function _load_result($multiple = FALSE)
	{
		$result = parent::_load_result($multiple);
		
		if ( ! $this->_saved AND ! $this->_loaded AND $this->empty_pk())
		{
			// Set the UUID primary key
			$this->_object[$this->_primary_key] = ORM::uuid();
			$this->_changed[$this->_primary_key] = $this->_primary_key;
		}
		
		return $result;
	}
	
	/**
	 * Load
	 *
	 * Override ORM _load method so a UUID can be set as the primary_key
	 * so we know what it is before the object has even been saved.
	 */
	protected function _load()
	{
		$result = parent::_load();
		
		if ( ! $this->_saved AND ! $this->_loaded AND $this->empty_pk())
		{
			// Set the UUID primary key
			$this->_object[$this->_primary_key] = ORM::uuid();
			$this->_changed[$this->_primary_key] = $this->_primary_key;
		}
		
		return $result;
	}
	
	/**
	 * Save
	 *
	 * Override ORM save method to assign a UUID primary key to an 
	 * object which may not yet have been assigned one.
	 */
	public function save()
	{
		if ($this->empty_pk())
		{
			// Set the UUID primary key
			$this->_object[$this->_primary_key] = ORM::uuid();
			$this->_changed[$this->_primary_key] = $this->_primary_key;
		}
		
		return parent::save();
	}
	
	/**
	 * Generate UUID
	 */
	public static function uuid()
	{
		return sprintf
		(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			
			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
				
			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),
				
			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,
			
			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,
			
			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	    );
	}
}