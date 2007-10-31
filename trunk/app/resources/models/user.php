<?
class User extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('username', 'string', 20);
        $this->hasColumn('password', 'string', 32);    
    }
}
?>