<?php
interface smp_iRegistry {
  public function set ($key, $value);
  public function get ($key);
  public function add ($key, $value);
  public function has ($key);
}