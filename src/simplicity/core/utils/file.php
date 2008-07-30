<?php
class smp_File
{

  private function __construct () {}

  private function __clone () {}
  
  static public function serialize ($file, $data)
  {
    $data = addslashes(serialize($data));
    $code = '<? \$data = unserialize("' . $data . '"); ?>';
    self::write($file, $code);
  }

  static public function unserialize ($file)
  {
    if (! file_exists($file))
    {
      return false;
    }
    ob_start();
    include ($file);
    ob_end_clean();
    if (isset($data))
    {
      return $data;
    }
    return false;
  }

  static public function write ($file, $data = "")
  {
    if (! $fp = (fopen($file, "w+")))
    {
      throw new Exception("Simplicity was not able to open the file: '$file' for writing. Please modify the directory settings to allow this.");
    }
    if (! flock($fp, LOCK_EX))
    {
      throw new Exception("Simplicity was not able to get an exclusive LOCK on the file: '$file'.");
    }
    $ret = fwrite($fp, $data);
    flock($fp, LOCK_UN);
    fclose($fp);
    return $ret;
  }

  static public function read ($file)
  {
    if (! file_exists($file))
    {
      throw new Exception("Simplicity was not able to find the following file: '$file'.");
    }
    if (! $fp = (fopen($file, "r")))
    {
      throw new Exception("Simplicity could not read the following file: '$file'.");
    }
    if (! flock($fp, LOCK_SH))
    {
      throw new Exception("Simplicity was not able to get a shared LOCK on the file: '$file'.");
    }
    $data = fread($fp, filesize($file));
    flock($fp, LOCK_UN);
    fclose($fp);
    return $data;
  }
}