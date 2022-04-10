<?php

namespace Ordnael\Configuration\Traits;

use RuntimeException;

/**
 * Trait with set of methods for implementing
 * cache in shared memory space.
 */
trait HasSharedMemoryCache
{
	/**
	 * Stores System V IPC key.
	 * 
	 * @var int
	 */
	private $ipc_key = -1;

	/**
	 * Stores SHM cache reference.
	 * 
	 * @var mixed
	 */
	private $shm_cache;

	/**
	 * Stores SHM cache semaphore.
	 * 
	 * @var mixed
	 */
	private $shm_lock;

	/**
	 * Amount of bytes allocated for shared memory.
	 * Set to 'null' to disable schema SHM cache.
	 * 
	 * @var int|null
	 */
	protected static $shm_cache_size = 10000; // n > 0 bytes

	/**
	 * Number of seconds for which the
	 * data will be available in cache.
	 * 
	 * @var int
	 */
	protected static $shm_cache_expires = 30; // n > 0 seconds

	/**
	 * Number of processes allowed
	 * to acquire SHM lock at once.
	 * 
	 * @var int
	 */
	protected static $shm_lock_max_acquire = 1; // n > 0 procs

	/**
	 * Method to setup SHM cache.
	 * 
	 * @param  string  $filename
	 * @return bool
	 * 
	 * @throws \RuntimeException
	 */
	private function setupCache(string $filename)
	{
		// No SHM cache is setup is its size is null;
		if (! static::$shm_cache_size) return true;

		if (! $this->shm_cache || ! $this->shm_lock) {
			$class = static::class;

			if (empty($filename) || ! file_exists($filename)) {
				$message = "[ {$class} ] '{$filename}': File not found.";
				
				throw new RuntimeException($message, 100);
			} else {
				$this->ipc_key = ftok($filename, 'x');
			}

			if ($this->ipc_key < 0) {
				$message = "[ {$class} ] Unable to generate SHM identifier for cache setup.";
				
				throw new RuntimeException($message, 101);
			}
			
			$this->shm_cache = shm_attach($this->ipc_key, static::$shm_cache_size, 0640);
			$this->shm_lock = sem_get($this->ipc_key, static::$shm_lock_max_acquire, 0640);

			if (! $this->shm_cache) {
				$message = "[ {$class} ] Unable to allocate SHM cache.";

				throw new RuntimeException($message, 102);
			}

			if (! $this->shm_lock) {
				$message = "[ {$class} ] Unable to receive SHM lock.";

				throw new RuntimeException($message, 103);
			}
		}

		return ($this->shm_cache && $this->shm_lock) ? true : false;
	}

	/**
	 * Method to purge SHM cache.
	 * 
	 * @return bool
	 * 
	 * @throws \RuntimeException
	 */
	private function purgeCache()
	{
		$status = [
			'class' => static::class,
			'blocked' => null,
			'removed' => null
		];

		if ($this->shm_lock) {
			$status['blocked'] = sem_remove($this->shm_lock);

			if (! $status['blocked']) {
				$message = "[ {$status['class']} ] Unable to remove SHM lock.";

				throw new RuntimeException($message, 203);
			}
		} else {
			$status['blocked'] = true;
		}

		if ($this->shm_cache) {
			$status['removed'] = shm_remove($this->shm_cache);

			if (! $status['removed']) {
				$message = "[ {$status['class']} ] Unable to remove SHM cache.";

				throw new RuntimeException($message, 202);
			}
		} else {
			$status['removed'] = true;
		}

		return ($status['blocked'] && $status['removed']) ? true : false;
	}

	/**
	 * Method to get key value from SHM cache.
	 * 
	 * @param  string  $key
	 * @return mixed|null
	 */
	protected function getFromCache(string $key)
	{
		$id = intval(sprintf('%u', crc32($key)));

		if (shm_has_var($this->shm_cache, $id)) {
			$data = shm_get_var($this->shm_cache, $id);

			if (array_key_exists($key, $data)) {
				$cached = $data[$key];
				$not_expired = ($cached['created'] + static::$shm_cache_expires) > time();

				return $not_expired ? $cached['value'] : null;
			}
		}

		return null;
	}

	/**
	 * Method to put key/value pair in SHM cache.
	 * 
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function putInCache(string $key, $value)
	{
		$id = intval(sprintf('%u', crc32($key)));
		sem_acquire($this->shm_lock);
		
		if (shm_has_var($this->shm_cache, $id)) {
			$data = self::clearExpiredKeys(shm_get_var($this->shm_cache, $id));
			$data[$key] = ['value' => $value, 'created' => time()];
		} else {
			$data = [$key => ['value' => $value, 'created' => time()]];
		}

		$cached = shm_put_var($this->shm_cache, $id, $data);
		sem_release($this->shm_lock);

		return $cached ? true : false;
	}

	/**
	 * Method to remove key/value pair from SHM cache.
	 * 
	 * @param  string  $key
	 * @return bool
	 */
	protected function delFromCache(string $key)
	{
		$id = intval(sprintf('%u', crc32($key)));
		sem_acquire($this->shm_lock);

		if (shm_has_var($this->shm_cache, $id)) {
			$data = shm_get_var($this->shm_cache, $id);
			
			if (! array_key_exists($key, $data)) return false;

			unset($data[$key]);

			if (! shm_put_var($this->shm_cache, $id, $data)) {
				sem_release($this->shm_lock);

				return false;
			}
		}
		
		sem_release($this->shm_lock);

		return true;
	}

	/**
	 * Method to filter out expired
	 * entries from raw SHM cache.
	 * 
	 * @param  array  $entries
	 * @return array<string, array>
	 */
	private static function clearExpiredKeys(array $entries)
	{
		return array_filter($entries, function ($data) {
			return ($data['created'] + static::$shm_cache_expires) > time();
		});
	}
}
