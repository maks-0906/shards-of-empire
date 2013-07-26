<?php
/**
 * Файл является API для постоянного соединения с клиентом
 */
class persistent_Action extends JSONAction
{
    public function RegisterEvent()
    {
        $this->AddEvent('dignity.json', 'actionGetCurrentDignity');
        //$this->SetDefaultEvent('default');
    }


    /**
     * Действие является API получения титула персонажа
     *
     * @throws StatusErrorException
     */
    public function actionGetCurrentDignity()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $state = personage_parameters_Dignity::model()->findAllStateDignity($idPersonage);

            if ($state->personage_state_fame >= $state->necessary_amount_fame AND $state->next_id_dignity != NULL) {

                $doneDignity = personage_State::model()->formPartOfIdDignity($state->next_id_dignity, $idPersonage);

                if ($doneDignity === true) {
	                $state = personage_parameters_Dignity::model()->findAllStateDignity($idPersonage);
	            }
            }

            

            //TODO: Сменить статические данные парметра персонажа "ЖИЗНЬ" при выяснении ТЗ
            $lifePersonage = personage_parameters_Life::model()->findLifePersonage($idPersonage);

            $this->Viewer_Assign('dignity', $state->name_dignity);
            $this->Viewer_Assign('personage_fame', $state->personage_state_fame);
            $this->Viewer_Assign('necessary_amount_fame', $state->necessary_amount_fame);
            $this->Viewer_Assign('personage_life', $lifePersonage['personage_life']);
            $this->Viewer_Assign('max_life', $lifePersonage['max_life']);

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'actionGetCurrentDignity` validate: ');
        } catch (Exception $e) {
            e1('Action `actionGetCurrentDignity` error: ', $e->getMessage());
        }
    }
}
