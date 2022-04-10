<?php

namespace Ordnael\Configuration;

use Stringable;
use Serializable;
use JsonException;
use Ordnael\Configuration\Remote\Database;
use Ordnael\Configuration\Traits\HasEncryptedValues;
use Ordnael\Configuration\Traits\HasSharedMemoryCache;
use Ordnael\Configuration\Exceptions\InvalidSchemaKeyException;
use Ordnael\Configuration\Exceptions\SchemaFailedCacheException;
use Ordnael\Configuration\Exceptions\SchemaFieldNotFoundException;

/**
 * Configuration schema class.
 */
class Schema extends Database implements Stringable, Serializable
{
	use HasEncryptedValues;
	use HasSharedMemoryCache;

	const FILENAME_TO_IPC_KEY = __FILE__;

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
	 * 
	 * @throws \Exceptions\InvalidSchemaKeyException
	 * @throws \Exceptions\SchemaFailedCacheException
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
		
		if (! $this->setupCache(self::FILENAME_TO_IPC_KEY)) {
			throw new SchemaFailedCacheException(
				self::FILENAME_TO_IPC_KEY . " > {$this->ipc_key} (SETUP)"
			);
		}
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
		$not_allowed = ['value'];

		if (in_array($attr, self::fields()) && ! in_array($attr, $not_allowed)) {
			return $this->{$attr};
		}

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
	 * 
	 * @throws \JsonException
	 */
	public function serialize()
	{
		$data = json_encode($this->toArray(), JSON_UNESCAPED_SLASHES);

		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new JsonException(json_last_error_msg(), json_last_error());
		}

		return $data;
	}

	/**
	 * Special method to unserialize object from string.
	 * 
	 * @param  string  $data
	 * @return void
	 * 
	 * @throws \JsonException
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
	 * Method for dropping SHM schema cache.
	 * 
	 * @return void
	 * 
	 * @throws \Exceptions\SchemaFailedCacheException
	 */
	protected function dropSchemaCache()
	{
		if (! $this->purgeCache()) {
			throw new SchemaFailedCacheException(
				self::FILENAME_TO_IPC_KEY . " > {$this->ipc_key} (PURGE)"
			);
		}
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
			'encrypted' => $this->encrypted,
			'context' => $this->context,
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
		return [
			'encrypted',
			'context',
			'key',
			'value'
		];
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
