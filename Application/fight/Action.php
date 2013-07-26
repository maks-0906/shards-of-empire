<?php
/**
 * Файл содержит логику обработки и управления боями
 *
 * @author vetalrakitin  <vetalrakitin@gfight.com>
 * @package fight
 */

/**
 * Класс управления логикой обработки данных GUI управления боями.
 *
 * @author vetalrakitin  <vetalrakitin@gfight.com>
 * @version 1.0.0
 * @package fight
 */
class fight_Action extends JSONAction
{

    /**
     * Register request uri for action method.
     */
    public function RegisterEvent()
    {
        //$this->AddEvent('create_new_fight.json', 'createFight', Auth::AUTH_USER);
        //$this->AddEvent('name.json', 'name', Auth::AUTH_USER);
        //$this->SetDefaultEvent('name.json');
         $this->AddEvent('test.json', 'testFight');
    }

    /**
     * Функция-событие создания нового боя
     *
     */
    public function createFight()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $this->Viewer_Assign('status', $this->status->successfully);
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `createFight` validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `createFight` error: ', $e->getMessage());
        }
    }

    public function testFight()
       {
           try {
              //$a = $destination = adminworld_Cell::model()->detectLocationAndHireOwner(97, 997, 0);
               fight_Mapper::model()->leadFight(1);

           } catch (JSONResponseErrorException $e) {
               $e->sendResponse($this, 'Action `createFight` validate: ');
           }
           catch (E1Exception $e) {
               e1('Action `createFight` error: ', $e->getMessage());
           }
       }
}
