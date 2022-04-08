<?php

namespace Ordnael\Configuration;

use Ordnael\Configuration\Remote\Database;
use Ordnael\Configuration\Traits\HasEncryptedValues;
use Stringable;
use Serializable;
use JsonException;
use Ordnael\Configuration\Exceptions\SchemaFieldNotFoundException;
use Ordnael\Configuration\Exceptions\InvalidSchemaKeyException;

/**
 * Configuration schema class.
 */
class Schema extends Database implements Stringable, Serializable
{
	use HasEncryptedValues;

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
	 * Static variable to store schema class name.
	 * Used for testing private and protected units.
	 * 
	 * @var string
	 */
	private static $schema_class = self::class;
	
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
		if (! self::isValidKey($key)) throw new InvalidSchemaKeyException($key);

		preg_match('%^(?<context>(?>[\w-]+\.)*)(?<key>[\w-]+)$%', $key, $group);

		if (isset($group['context'])) $this->context = trim($group['context'], '.');

		$this->key = $group['key'];
		$this->value = $val;
		$this->encrypted = $encrypted;
	}

	/**
	 * Special method to set object attributes.
	 * 
	 * @param  string  $attr
	 * @param  mixed   $val
	 * @return mixed
	 * 
	 * @throws \Exceptions\SchemaFieldNotFoundException
	 */
	public function __set(string $attr, $val)
	{
		// Attributes allowed to be set by others
		$allowed = [];

		if (in_array($attr, $allowed)) {
			$this->{$attr} = $val;
		} else {
			throw new SchemaFieldNotFoundException($attr);
		}
	}

	/**
	 * Special method to get object attributes.
	 * 
	 * @param  string  $attr
	 * @return mixed
	 * 
	 * @throws \Exceptions\SchemaFieldNotFoundException
	 */
	public function __get(string $attr)
	{
		// Attributes not allowed to be retrieved by others
		$notallowed = ['value'];
		
		$allowed = array_filter(
			self::fields(),
			function ($attr) use ($notallowed) {
				return ! in_array($attr, $notallowed);
			}
		);

		if (in_array($attr, $allowed)) return $this->{$attr};

		throw new SchemaFieldNotFoundException($attr);
	}

	/**
	 * Special method to represent object as string.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$value = $this->value(false);

		if (is_array($value)) $value = json_encode($value, JSON_UNESCAPED_SLASHES);

		return sprintf('[ %s ] "%s": %s (%s)', static::$schema_class,
			"{$this->context}.{$this->key}",
			$value ?? 'null',
			$this->encrypted ? 'encrypted' : 'not-encrypted'
		);
	}

	/**
	 * Special method to serialize object to array.
	 * 
	 * @return array<string, mixed>
	 */
	public function __serialize()
	{
		return $this->toArray();
	}

	/**
	 * Special method to unserialize object from array.
	 * 
	 * @param  array  $data
	 * @return void
	 */
	public function __unserialize(array $data)
	{
		$this->context = $data['context'];
		$this->encrypted = $data['encrypted'];
		$this->key = $data['key'];
		$this->value = $data['value'];
	}

	/**
	 * Special method to serialize object to string.
	 * 
	 * @return string
	 */
	public function serialize()
	{
		$data = json_encode($this->toArray(), JSON_UNESCAPED_SLASHES);

		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new JsonException(json_last_error_msg());
		}

		return $data;
	}

	/**
	 * Special method to unserialize object from string.
	 * 
	 * @param  string  $data
	 * @return void
	 */
	public function unserialize(string $data)
	{
		$data = json_decode($data);
		
		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new JsonException(json_last_error_msg());
		}

		$this->context = $data->context;
		$this->encrypted = $data->encrypted;
		$this->key = $data->key;
		$this->value = $data->value;
	}

	/**
	 * Method to return object's configuration value.
	 * 
	 * @param  bool  $decrypt
	 * @return mixed
	 */
	protected function value(bool $decrypt = true)
	{
		if ($decrypt && $this->encrypted) return self::decrypt($this->value);

		return $this->value;
	}

	/**
	 * Method for converting object into array.
	 * 
	 * @return array<string, mixed>
	 */
	private function toArray()
	{
		return [
			'schema_class' => static::$schema_class,
			'context' => $this->context,
			'encrypted' => $this->encrypted,
			'key' => $this->key,
			'value' => $this->value(false)
		];
	}

	/**
	 * Returns configuration schema fields.
	 * 
	 * @return array<int, string>
	 */
	final public static function fields()
	{
		// Properties not to be listed as schema fields
		$remove = ['schema_class'];

		$schema_fields = array_filter(
			array_keys(get_class_vars(self::class)),
			function ($field) use ($remove) {
				return ! in_array($field, $remove);
			}
		);
		
		return $schema_fields;
	}

	/**
	 * Static method to validate if string is a valid key.
	 * 
	 * @param  string  $key
	 * @return bool
	 */
	final public static function isValidKey(string $key)
	{
		return preg_match('%^[\w-]+((\.[\w-]+)*|[^\.\s])$%', $key) ? true : false;
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
		if ($encrypted) $val = self::encrypt($val);

		return new static::$schema_class($key, $val, $encrypted);
	}
}
