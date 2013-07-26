<?php
/**
 * Description content file
 *
 * @author Greg
 * @package
 */

interface ResourceStrategy
{

    public function resourceCalculation();
}

/**
 * Description class
 *
 * @author Greg
 * @version
 * @package
 */
class building_ResourceStrategy_Factory
{

    /**
     *
     * @param string $nameStrategy
     * @return bool
     * @throws ErrorException
     */
    public static function getStrategy($nameStrategy)
    {
        switch ($nameStrategy) {
            case 'normal':
                return new building_ResourceStrategy_Base();
                break;

            case 'production':
                return new building_ResourceStrategy_Production();
                break;

            default:
                throw new ErrorException('Strategy by name: ' . $nameStrategy . ' not found');
        }
    }
}
