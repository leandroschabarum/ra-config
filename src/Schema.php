<?php

namespace Ordnael\Configuration;

/**
 * Configuration schema class.
 */
class Schema
{
	/**
	 * Flags if configuration value is or
	 * should be encrypted in storage.
	 * 
	 * @var bool
	 */
	private $encrypted = false;

	/**
	 * Keeps track of configuration context.
	 * 
	 * @var string
	 */
	private $context = 'global';

	/**
	 * Stores the configuration key name.
	 * 
	 * @var string
	 */
	private $key;

	/**
	 * Stores the configuration key value.
	 * 
	 * @var mixed
	 */
	private $value;
	
	/**
	 * Configuration schema constructor.
	 * 
	 * @param  string  $key
	 * @param  mixed   $val
	 * @param  bool    $encrypted
	 * @return $this
	 */
	protected function __construct(string $key, $val, bool $encrypted = false)
	{
		/*
		| KEY is 'key' ............. CONTEXT is 'global' (default) ... STORED as 'global.key'
		| KEY is 'ctx1.key' ........ CONTEXT is 'ctx1' ............... STORED as 'ctx1.key'
		| KEY is 'ctx1.ctx2.key' ... CONTEXT is 'ctx1.ctx2' .......... STORED as 'ctx1.ctx2.key'
		*/
		$this->encrypted = $encrypted;
		$this->context = $key;
		$this->key = $key;
		$this->value = $val;
	}

	/**
	 * Special method to retrieve object attributes.
	 * 
	 * @param  string  $attr
	 * @return mixed
	 * 
	 * @throws \Exceptions\SchemaFieldNotFound
	 */
	public function __get(string $attr)
	{
		$allowed = self::fields();

		if (($i = array_search('value', $allowed)) !== false) unset($allowed[$i]);

		if (in_array($attr, $allowed)) return $this->{$attr};

		/** @todo Missing SchemaFieldNotFound Exception class! */
		throw new Exception("'{$attr}' field is missing from configuration schema.");
	}

	/**
	 * Method to return object's configuration value.
	 * 
	 * @param  bool  $decrypt
	 * @return mixed
	 */
	protected function value(bool $decrypt = true)
	{
		/** @todo Missing decrypt() method implementation! */
		if ($decrypt && $this->encrypted) return decrypt($this->value);

		return $this->value;
	}

	/**
	 * Returns configuration schema fields.
	 * 
	 * @return array<int, string>
	 */
	public static function fields()
	{
		return array_keys(get_class_vars(self::class));
	}

	/**
	 * Static method to create new configuration schema entry.
	 * 
	 * @param  string  $key
	 * @param  mixed   $val
	 * @param  bool    $encrypted
	 * @return \Schema
	 */
	protected static function create(string $key, $val, bool $encrypted = false)
	{
		/** @todo Missing encrypt() method implementation! */
		if ($encrypted) $val = encrypt($val);

		return new Schema($key, $val, $encrypted);
	}
}
