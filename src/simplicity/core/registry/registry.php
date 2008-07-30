<?php
class smp_Registry implements smp_iRegistry
{

  private $_data;

  /**
   * Sets a $key in the registry to $value.
   *
   * @param string $key
   * @param mixed $value
   *
   */
  public function set ($key, $value)
  {
    $this->_data[$key] = $value;
  }

  /**
   * Gets a $key from the registry. Returns null if the key does not exist.
   *
   * @param string $key
   * @return mixed
   */
  public function get ($key)
  {
    return $this->has($key) ? $this->_data[$key] : null;
  }

  /**
   * Adds a $key to the registry with $value but only if the key does not exist.
   *
   * @param string $key
   * @param mixed $value
   */
  public function add ($key, $value)
  {
    if (! $this->has($key))
    {
      $this->set($key, $value);
    }
  }

  /**
   * Returns bool true / false if the registry has $key.
   *
   * @param string $key
   * @return bool
   */
  public function has ($key)
  {
    return isset($this->_data[$key]);
  }
}