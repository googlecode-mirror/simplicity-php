<?php
class smp_TreeNodeManager {
  
  static private $_roots;
  static private $_cache;
  
  static public function registerRootNode(smp_TreeNode $node) {    
    $id = $node->getId();
    
    if (isset(self::$_roots[$id])) {
      throw new Exception("Cannot replace existing root node with id '{$id}'.");
      return false;
    }
    
    self::$_roots[$id] = $node;
    return self::$_roots[$id];
  }
  
  static public function destroyRootNode($id) {
    unset(self::$_roots[$id]);
  }
  
  static public function clearCache($path) {
    //TODO: Implement this!
  }
  
  static public function findNode($path) {   
    if (isset(self::$_cache[$path])) {
      return self::$_cache[$path];
    }
    
    $cache = $path;
    
    $path = explode('.',$path);
    if (!count($path)) {
      return false;
    }
    $id = array_shift($path);
    
    if (!isset(self::$_roots[$id])) {
      return false;
    }
    
    $node = self::$_roots[$id];
    
    if (!count($path)) {
      self::$_cache[$cache] = $node;
      return self::$_cache[$cache];
    }
    
    $id = array_shift($path);
    while($node->hasChild($id)) {
      $node = $node->getChild($id);
      
      if (!count($path)) {
        self::$_cache[$cache] = $node;
        return self::$_cache[$cache];
      }
      
      $id = array_shift($path);
    }
    
    return false;
  }
  
}