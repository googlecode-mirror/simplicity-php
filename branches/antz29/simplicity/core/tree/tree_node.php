<?php
/**
 * Contains the smp_TreeNode class definition.
 * 
 * This file defines the smp_TreeNode class.
 * 
 * @author John Le Drew <jp@antz29.com>
 * @copyright Copyright (c) 2008, John Le Drew
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser Public License 3.0
 * @package core
 * @subpackage tree
 */

/**
 * The 
 */
class smp_TreeNode implements ArrayAccess
{
  private $_id;
  private $_attributes = array();
  
  private $_root;
  private $_parent;
  private $_children;
  
  private $_path;
  
  static public function createRootNode ($id)
  {
    $node = new smp_TreeNode($id);
    return smp_TreeNodeManager::registerRootNode($node);
  }
  
  public function createNode ($id)
  {
    return new smp_TreeNode($id, $this);
  }
  
  private function __construct ($id, smp_TreeNode $root = null)
  {
    $id = strtolower($id);
    if (! ctype_alnum($id))
    {
      throw new InvalidArgumentException('$id should be a string containing only characters a-z & 0-9');
    }
    
    $this->_id = $id;
    
    if ($root instanceof smp_TreeNode)
    {
      $this->_root = $root;
    } else
    {
      $this->_root = $this;
    }
    
    $this->_children = new smp_TreeNodeIterator($this);
  }
  
  public function getId ()
  {
    return $this->_id;
  }
  
  private function setParent (smp_TreeNode $parent)
  {
    $this->_parent = $parent;
  }
  
  public function getParent ()
  {
    return $this->_parent;
  }
  
  public function getRoot ()
  {
    return $this->_root;
  }
  
  public function isRoot ()
  {
    if (! ($this->_parent instanceof smp_TreeNode))
    {
      return true;
    } else
    {
      return false;
    }
  }
  
  public function hasAttribute ($name)
  {
    return isset($this->_attributes[$name]);
  }
  
  public function getAttribute ($name)
  {
    return $this->hasAttribute($name) ? $this->_attributes[$name] : null;
  }
  
  public function setAttribute ($name, $value)
  {
    $this->_attributes[$name] = $value;
    return (isset($this->_attributes[$name]) && $this->_attributes[$name] == $value);
  }
  
  public function removeAttribute ($name)
  {
    if ($this->hasAttribute($name))
    {
      unset($this->_attributes[$name]);
      return true;
    } else
    {
      return false;
    }
  }
  
  public function __call ($name, $args = array())
  {
    
    $action = substr($name, 0, 3);
    $name = smp_String::underscore(substr($name, 3));
    
    switch ($action)
    {
      case 'has':
        return $this->hasAttribute($name);
        break;
      case 'get':
        return $this->getAttribute($name);
        break;
      case 'set':
        if (! isset($args[0]))
        {
          return false;
        }
        return $this->setAttribute($name, $args[0]);
        break;
      case 'del':
        return $this->removeAttribute($name);
        break;
    }
  }
  
  public function getPath ()
  {
    if (! isset($this->_path))
    {
      $path[] = $this->getId();
      while ($node = $this->getParent())
      {
        $path[] = $node->getId();
      }
      $this->_path = implode('.', array_reverse($path));
    }
    return $this->_path;
  }
  
  public function find ($path)
  {
    $path = $this->getPath() . '.' . $path;
    return smp_TreeNodeManager::findNode($path);
  }
  
  public function addChild (smp_TreeNode $child)
  {
    if (spl_object_hash($this) == spl_object_hash($child))
    {
      return false;
    }
    
    foreach ($this->getAscendants() as $asc)
    {
      if (spl_object_hash($asc) == spl_object_hash($child))
      {
        return false;
      }
    }
    
    $child->setParent($this);
    $this->_children->addNode($child);
    
    return true;
  }
  
  public function removeChild ($id)
  {
    return $this->_children->delNode($id);
  }
  
  public function getChild ($id)
  {
    return $this->_children->getNode($id);
  }
  
  public function hasChildren ()
  {
    return count($this->_children) ? true : false;
  }
  
  public function getChildren ()
  {
    return $this->_children;
  }
  
  public function getAscendants ()
  {
    $asc = new smp_TreeNodeIterator();
    if ($node = $this->getParent())
    {
      $asc->addNode($node);
      while ($node = $node->getParent())
      {
        $asc->addNode($node);
      }
    }
    return $asc;
  }
  
  public function getDescendants ()
  {
    $dsc = new smp_TreeNodeIterator();
    if ($this->hasChildren())
    {
      foreach ($this->getChildren() as $child)
      {
        $dsc->addNode($child);
        foreach ($child->getDescendants() as $node)
        {
          $dsc->addNode($node);
        }
      }
    }
    return $dsc;
  }
  
  public function getSiblings ()
  {
    $sib = new smp_TreeNodeIterator();
    if ($this->getParent() && $this->getParent()->hasChildren())
    {
      foreach ($this->getParent()->getChildren() as $sibling)
      {
        if (spl_object_hash($sibling) != spl_object_hash($this))
        {
          $sib->addNode($sibling);
        }
      }
    }
    return $sib;
  }
  
  public function __get ($id)
  {
    switch ($id)
    {
      case 'ascendants':
        return $this->getAscendants();
        break;
      case 'descendants':
        return $this->getDescendants();
        break;
      case 'children':
        return $this->getChildren();
        break;
      case 'siblings':
        return $this->getSiblings();
        break;
      case 'parent':
        return $this->getParent();
        break;
    }
    return false;
  }
}