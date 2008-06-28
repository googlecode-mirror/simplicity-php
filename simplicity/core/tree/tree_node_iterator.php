<?php

class smp_TreeNodeIterator implements Iterator,Countable,ArrayAccess
{
  private $_nodes;
  private $_root;
  
  public function __construct ()
  {
    $args = func_get_args();
    
    switch (count($args)) {
      case 0:
      case 1:
        if (isset($args[0]) && $args[0] instanceof smp_TreeNode) {
          $nodes = array();
          $this->_root = $args[0];
        } else {
          if (isset($args[0]) && is_array($args[0])) {
            $nodes = $args[0];
          } else {
            $nodes = array();
          }
        }
        break;
      case 2:
        if ($args[0] instanceof smp_TreeNode) {
          $this->_root = $args[0];
          $nodes = $args[1];
        } else {
          throw new InvalidArgumentException('When providing both parameters, 1st parameter should be the root node and the second should be the node array.');
        }
        break;
    }
    
    foreach ($nodes as $node) {
      if ($this->_root instanceof smp_TreeNode)
      {
        $this->_nodes[$node->getId()] = $node;
      } else
      {
        $this->_nodes[$node->getPath()] = $node;
      }
    }
  }
  
  public function count() {
    return count($this->_nodes);
  }
  
  public function current ()
  {
    return $this->offsetGet($this->key());
  }
  
  public function key ()
  {
    return key($this->_nodes);
  }
  
  public function next ()
  {
    next($this->_nodes);
  }
  
  public function rewind ()
  {
    reset($this->_nodes);
  }
  
  public function valid ()
  {
    return (current($this->_nodes) !== FALSE);
  }
  
  public function addNode (smp_TreeNode $node)
  {
    if ($this->_root instanceof smp_TreeNode)
    {
      $this->_nodes[$node->getId()] = $node;
    } else
    {
      $this->_nodes[$node->getPath()] = $node;
    }
  }
  
  public function delNode ($path)
  {
    unset($this->_nodes[$path]);
  }
  
  public function hasNode ($path)
  {
    return isset($this->_nodes[$path]);
  }
  
  public function getNode($path) {
    if (! $this->hasNode($path))
    {
      return null;
    }
    return $this->_nodes[$path];
  }
  
  public function offsetSet($key,smp_TreeNode $node) {
    if ($key == '*') {
      return $this->addNode($node);
    } else {
      return false;
    }
  }
  
  public function offsetGet($path) {
    return $this->getNode($path);
  }
  
  public function offsetExists($path) {
    return $this->hasNode($path);
  }
  
  public function offsetUnset($path) {
    return $this->delNode($path);
  }
}