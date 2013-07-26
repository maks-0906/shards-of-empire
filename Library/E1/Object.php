/*
<zz?php
/**
 * parent project class
 */
abstract class Object {
/* Откладываем Hooks до повсемесного 5.3
    static private $System_Hooks =array();
    //TODO: Доработать механизм Хуков для вызова статических экземпляров
    /**
     *
     * @param strrng $HookName - Capture Name
     * @param type $Callback   - launch static callback
     */
    /*
    static public function HookRegister($HookName, $sClass, $sMethod) {
        if (!class_exists($sClass)) {e1('Hook Object',$sClass,' not exist'); die();}
        $oRefMethod = new ReflectionMethod($sClass, $sMethod);
        if (!$oRefMethod->isStatic()) {e1('Hook Object',$sClass,' have not static method ',$sMethod); die();}
        if (!isset(self::$System_Hooks[$HookName])) {
            self::$System_Hooks[$HookName] = array ();
        }
        array_push(self::$System_Hooks[$HookName], $sClass.'::'.$sMethod);
    }
    //TODO: Может добавить HookPRegRegister($HookPRegMask, $Callback)
    
    public function HookRun($HookName) {
        if (!isset(self::$System_Hooks[$HookName])) {return false;}
        foreach (self::$System_Hooks[$HookName] as $Callback) {
            //Running will here    
            e1($Callback);
        }
    }  
*/
    /**
     * Class Factory Get Some Instance (fullname required)
     * @param string $sClass - class Application
     * @param array $aArgs - __construct params
     * @return Object  - instance of $sClass
     * e.g 
     * $this->Get('User_Mapper', __construct params); 
     * 
     */
    public function Get($sClass, $aArgs = null) {
        return new $sClass($aArgs);
    }

}

*/
