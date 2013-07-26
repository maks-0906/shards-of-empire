<?php
/**
 * Файл содержит логику обработки и управления союзами
 */

class guild_Action extends JSONAction
{

    /**
     * Register request uri for action method.
     */
    public function RegisterEvent()
    {
        $this->AddEvent('create_guild.json', 'actionCreateGuild', Auth::AUTH_USER);
        $this->AddEvent('accept_personage.json', 'actionAcceptPersonage', Auth::AUTH_USER);
        $this->AddEvent('kick_personage.json', 'actionKickPersonage', Auth::AUTH_USER);
        $this->AddEvent('all_guild.json', 'actionAllGuild', Auth::AUTH_USER);
        $this->AddEvent('info_my_guild.json', 'actionInfoMyGuild', Auth::AUTH_USER);
        $this->AddEvent('leave.json', 'actionLeaveGuild', Auth::AUTH_USER);
        $this->AddEvent('my_request.json', 'actionMyRequest', Auth::AUTH_USER);
        $this->AddEvent('my_invitation.json', 'actionMyInvitation', Auth::AUTH_USER);
        $this->AddEvent('delete_my_request.json', 'actionDeleteMyRequest', Auth::AUTH_USER);
        $this->AddEvent('delete_my_invitation.json', 'actionDeleteMyInvitation', Auth::AUTH_USER);
        $this->AddEvent('apply_request.json', 'actionApplyRequest', Auth::AUTH_USER);
        $this->AddEvent('apply_invitation.json', 'actionApplyInvitation', Auth::AUTH_USER);
        //$this->SetDefaultEvent('name.json');
    }

    /**
     * Функция-событие создания нового союза
     *
     */
    public function actionCreateGuild()
    {
        try {
            // Проверяем, чтобы персонаж был залогинен
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $name = $this->GetVar('name');
            if ($name == NULL)
                throw new StatusErrorException('Parameter `name` not defined', $this->status->main_errors);

            $type = $this->GetVar('type');
            if ($type == NULL)
                throw new StatusErrorException('Parameter `type` not defined', $this->status->main_errors);

            $personageState = personage_State::model()->findStatePersonageById($idPersonage);

            if ($personageState->guild_id != guild_Mapper::NOT_GUILD) {
                throw new StatusErrorException('Personage already in guild and can not create new guild', $this->status->main_errors);
            }

            //Проводим валидацию типа союза
            if ($type != guild_Mapper::GUILD_TYPE_INTERFRACTIONAL AND
                $type != guild_Mapper::GUILD_TYPE_FRACTIONAL
            ) {
                throw new StatusErrorException('Parameter `type` incorrect', $this->oStatus->main_errors);
            }

            //Создаем новый союз
            $doneNewGuild = guild_Mapper::model()->createGuild($name, $type, $idPersonage);

            if ($doneNewGuild === true) {
                $this->Viewer_Assign('status', $this->status->successfully);
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `actionCreateGuild` validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `actionCreateGuild` error: ', $e->getMessage());
        }
    }

    /**
     * Функция-событие принять персонажа в союз
     *
     */
    public function actionAcceptPersonage()
    {
        try {
            // Проверяем, чтобы персонаж-вербовщик (Владелец или модератор) был залогинен
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            // ИД персонажа, которого принимаем в союз
            $idAcceptPersonage = $this->GetVar('id_personage');
            if ($idAcceptPersonage == NULL)
                throw new StatusErrorException('Parameter `id_personage` not defined', $this->status->main_errors);

            $idGuild = $this->GetVar('id_guild');
            if ($idGuild == NULL)
                throw new StatusErrorException('Parameter `id_guild` not defined', $this->status->main_errors);

            // Проверяем, достаточно ли прав у персонажа-вербовщика, для добавления персонажа в союз
            $roleGuildPersonage = $personageState = personage_State::model()->findStatePersonageById($idPersonage);
            if ($roleGuildPersonage->role_in_guild != guild_Mapper::GUILD_ROLE_MODER &&
                $roleGuildPersonage->role_in_guild != guild_Mapper::GUILD_ROLE_OWNER
            )
                throw new StatusErrorException('You do not have sufficient rights to add personage to the guild', $this->status->main_errors);

            // Проверяем, не состоит ли персонаж уже в союзе
            $personageState = personage_State::model()->findStatePersonageById($idAcceptPersonage);

            if ($personageState->guild_id != guild_Mapper::NOT_GUILD) {
                throw new StatusErrorException('Personage already in guild and can not add to guild', $this->status->main_errors);
            }

            //Принять персонажа в союз
            if (!guild_Mapper::model()->updateRoleGuildPersonage($idAcceptPersonage, $idGuild, guild_Mapper::GUILD_ROLE_MEMBER))
                throw new StatusErrorException('Unable create personage to guild', $this->status->main_errors);

            $this->Viewer_Assign('status', $this->status->successfully);
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `actionAcceptPersonage` validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `actionAcceptPersonage` error: ', $e->getMessage());
        }
    }

    /**
     * Функция-событие выгнать персонажа из союза
     *
     */
    public function actionKickPersonage()
    {
        try {
            // Проверяем, чтобы персонаж был залогинен
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            // ИД персонажа, которого выгоняем из союза
            $idKickPersonage = $this->GetVar('id_kick_personage');
            if ($idKickPersonage == NULL)
                throw new StatusErrorException('Parameter `id_personage` not defined', $this->status->main_errors);

            $idGuild = $this->GetVar('id_guild');
            if ($idGuild == NULL)
                throw new StatusErrorException('Parameter `id_guild` not defined', $this->status->main_errors);

            // Проверяем какая роль в союзе у персонажа
            $roleGuildPersonage = $personageState = personage_State::model()->findStatePersonageById($idPersonage);
            if ($roleGuildPersonage->role_in_guild != guild_Mapper::GUILD_ROLE_MODER &&
                $roleGuildPersonage->role_in_guild != guild_Mapper::GUILD_ROLE_OWNER
            ) {
                throw new StatusErrorException('You can not drive out the character of the guild', $this->status->main_errors);
            }

            //Выгоняем персонажа из союза(гильдии)
            if (!guild_Mapper::model()->updateRoleGuildPersonage($idKickPersonage, guild_Mapper::NOT_GUILD,
                guild_Mapper::GUILD_ROLE_NOTUNION)
            )
                throw new StatusErrorException('Unable add personage to guild', $this->status->main_errors);

            $this->Viewer_Assign('status', $this->status->successfully);
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `actionKickPersonage` validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `actionKickPersonage` error: ', $e->getMessage());
        }
    }

    /**
     * Действие передает клиентской части список всех союзов
     */
    public function actionAllGuild()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $allGuilds = guild_Mapper::model()->findOwnerGuildsAndTotalPersonage();
            $ownersGuilds = $this->_formedResponseResult($allGuilds);

            $this->Viewer_Assign('all_guilds', $ownersGuilds);

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `actionAllGuild` validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `actionAllGuild` error: ', $e->getMessage());
        }
    }

    /**
     * Действие передает клинтской части данные об союзе персонажа
     *
     * @throws StatusErrorException
     */
    public function actionInfoMyGuild()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $myBonusesAndExperience = array();
            $guildPersonage = guild_Mapper::model()->findGuildOnIdPersonage($idPersonage);

            if (empty($guildPersonage)) {
                throw new StatusErrorException('Not data info my guild', $this->status->main_errors);
            }

            //Проводим валидацию уровня союза, для получения бонуса текущего и последующего
            if ($guildPersonage->max_level_guilds > $guildPersonage->level_guild_personage) {
                $currentLevelGuild = $guildPersonage->level_guild_personage;
                $nextLevelGuild = $guildPersonage->level_guild_personage + 1;
            } else {
                $currentLevelGuild = $guildPersonage->level_guild_personage;
                $nextLevelGuild = $guildPersonage->level_guild_personage;
            }

            $currentBonusAndExperience = guild_BonusesExperience::model()->findBonusesAndExperienceGuildOnLevel($currentLevelGuild);
            $nextBonusAndExperience = guild_BonusesExperience::model()->findBonusesAndExperienceGuildOnLevel($nextLevelGuild);


            $myBonusesAndExperience = $this->_formedResponseBonusesAndExperience($currentBonusAndExperience, $nextBonusAndExperience);
            $myBonusesAndExperience['name_guild_personage'] = $guildPersonage->name_guild_personage;
            $myBonusesAndExperience['level_guild_personage'] = $guildPersonage->level_guild_personage;
            $myBonusesAndExperience['current_experience'] = $guildPersonage->current_experience_guild;

            if (empty($myBonusesAndExperience)) {
                throw new StatusErrorException('Not data my guilds', $this->status->main_errors);
            }

            $allMemberMyGuild = guild_Mapper::model()->findAllMemberGuilds($guildPersonage->guild_id, $idPersonage);

            $this->Viewer_Assign('info_my_guilds', $myBonusesAndExperience);
            $this->Viewer_Assign('members', $this->_formedResponseResult($allMemberMyGuild));

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `actionInfoMyGuild` validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `actionInfoMyGuild` error: ', $e->getMessage());
        }
    }


    /**
     * Действие обеспечивает логику когда персонаж покидает союз
     *
     * @throws StatusErrorException
     */
    public function actionLeaveGuild()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            // Проверяем какая роль в союзе у персонажа
            $roleGuildPersonage = $personageState = personage_State::model()->findStatePersonageById($idPersonage);

            if ($roleGuildPersonage->role_in_guild == guild_Mapper::GUILD_ROLE_MODER OR
                $roleGuildPersonage->role_in_guild == guild_Mapper::GUILD_ROLE_OWNER
            ) {
                throw new StatusErrorException('You can not leave the guild', $this->status->main_errors);
            }

            //Персонаж покидает союз
            if (!guild_Mapper::model()->updateRoleGuildPersonage($idPersonage, guild_Mapper::NOT_GUILD,
                guild_Mapper::GUILD_ROLE_NOTUNION)
            ) {
                $this->Viewer_Assign('status', $this->status->main_errors);
            } else {
                $this->Viewer_Assign('status', $this->status->successfully);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `actionLeaveGuild` validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `actionLeaveGuild` error: ', $e->getMessage());
        }
    }


    /**
     * Действие для получения всех заявок персонажа на союз
     *
     * @throws StatusErrorException
     */
    public function actionMyRequest()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $myAllRequest = guild_RequestInvitation::model()->findAllStatusRequestOrInvitation(guild_RequestInvitation::GUILD_REQUEST,
                $idPersonage);

            if (!empty($myAllRequest)) {
                $this->Viewer_Assign('my_request', $this->_formedRequestOrInvitation($myAllRequest));
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `actionMyRequest` validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `actionMyRequest` error: ', $e->getMessage());
        }
    }

    /**
     * Действие для получения всех приглашений персонажа на союз
     *
     * @throws StatusErrorException
     */
    public function actionMyInvitation()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $myAllInvitation = guild_RequestInvitation::model()->findAllStatusRequestOrInvitation(guild_RequestInvitation::GUILD_INVITATION,
                $idPersonage);

            if (!empty($myAllInvitation)) {
                $this->Viewer_Assign('my_invitation', $this->_formedRequestOrInvitation($myAllInvitation));
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `actionMyInvitation` validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `actionMyInvitation` error: ', $e->getMessage());
        }
    }

    /**
     * Действие по удалении персонажем своих заявок
     * @throws StatusErrorException
     */
    public function actionDeleteMyRequest()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $idRequest = $this->GetVar('id_request');
            if ($idRequest == NULL)
                throw new StatusErrorException('Parameter id_request not defined', $this->status->main_errors);

            //Получаем данные по заявке
            $myRequest = guild_RequestInvitation::model()->findRequestOrInvitationOnId($idRequest);
            if ($myRequest == NULL)
                throw new StatusErrorException('No data on the request in the guild', $this->status->main_errors);

            //Проверяем явлется ли эта заявка на союз данного персонажа
            if ($idPersonage != $myRequest->id_personage)
                throw new StatusErrorException('You can not delete', $this->status->main_errors);

            //Проверяем действительно это заявка
            if ($myRequest->status_request_invitation != guild_RequestInvitation::GUILD_REQUEST)
                throw new StatusErrorException('You can not delete because it is not an request', $this->status->main_errors);

            //Удаляем заявку персонажа на союз
            $doneDeleteRequest = guild_RequestInvitation::model()->deleteRequestOrInvitation($idRequest);

            if ($doneDeleteRequest === true) {
                $this->Viewer_Assign('status', $this->status->successfully);
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `actionDeleteMyRequest` validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `actionDeleteMyRequest` error: ', $e->getMessage());
        }
    }

    /**
     * Действие по удалении персонажем своих приглашений
     *
     * @throws StatusErrorException
     */
    public function actionDeleteMyInvitation()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $idInvitation = $this->GetVar('id_invitation');
            if ($idInvitation == NULL)
                throw new StatusErrorException('Parameter id_invitation not defined', $this->status->main_errors);

            //Получаем данные о приглашениии в союз
            $myInvitation = guild_RequestInvitation::model()->findRequestOrInvitationOnId($idInvitation);
            if ($myInvitation == NULL)
                throw new StatusErrorException('No data on the request in the guild', $this->status->main_errors);

            //Проверяем явлется ли эта приглашение на союз данного персонажа
            if ($idPersonage != $myInvitation->id_personage)
                throw new StatusErrorException('You can not delete', $this->status->main_errors);

            //Проверяем действительно это приглашение
            if ($myInvitation->status_request_invitation != guild_RequestInvitation::GUILD_INVITATION)
                throw new StatusErrorException('You can not delete because it is not an invitation', $this->status->main_errors);

            //Удаляем приглашение персонажа на союз
            $doneDeleteInvitation = guild_RequestInvitation::model()->deleteRequestOrInvitation($idInvitation);

            if ($doneDeleteInvitation === true) {
                $this->Viewer_Assign('status', $this->status->successfully);
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `actionDeleteMyRequest` validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `actionDeleteMyRequest` error: ', $e->getMessage());
        }
    }

    /**
     * Действие предназначено для добавления заявки на союз
     *
     * @throws StatusErrorException
     */
    public function actionApplyRequest()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $idGuild = $this->GetVar('id_guild');
            if ($idGuild == NULL)
                throw new StatusErrorException('Parameter id_guild not defined', $this->status->main_errors);

            //TODO: Необходима проверка на уровень симпатии персонажа
            //Получаем данные по заявке
            $myRequest = guild_RequestInvitation::model()->findRequestOrInvitationOnIdGuild($idGuild, $idPersonage);
            var_dump($myRequest);
            if ($myRequest != NULL)
                throw new StatusErrorException('The request already exists', $this->status->main_errors);

            //Добавляем заявку персонажа на союз
            $doneInsertRequest = guild_RequestInvitation::model()->insertRequestOrInvitation($idPersonage, $idGuild,
                guild_RequestInvitation::GUILD_REQUEST);

            if ($doneInsertRequest === true) {
                $this->Viewer_Assign('status', $this->status->successfully);
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `actionDeleteMyRequest` validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `actionDeleteMyRequest` error: ', $e->getMessage());
        }
    }

    /**
     * Действие предназначено для добавления заявки на союз
     *
     * @throws StatusErrorException
     */
    public function actionApplyInvitation()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $idGuild = $this->GetVar('id_guild');
            if ($idGuild == NULL)
                throw new StatusErrorException('Parameter id_guild not defined', $this->status->main_errors);

            //ID приглашаемого персонажа
            $idInvitedPersonage = $this->GetVar('id_invited_personage');
            if ($idInvitedPersonage == NULL)
                throw new StatusErrorException('Parameter id_invited_personage not defined', $this->status->main_errors);

            //Проверяем существует ли приглашаемый персонаж в текущем мире
            $invitedPersonage = personage_Mapper::model()->findPersonageByIdAndIdWorld($idInvitedPersonage, Auth::getCurrentWorldId());
            if ($invitedPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined to be invited into an guild', $this->status->personage_not_exists);

            // Проверяем, достаточно ли прав у персонажа-вербовщика, для приглашения персонажа в союз
            $roleGuildPersonage = $personageState = personage_State::model()->findStatePersonageById($idPersonage);
            if ($roleGuildPersonage->role_in_guild != guild_Mapper::GUILD_ROLE_MODER &&
                $roleGuildPersonage->role_in_guild != guild_Mapper::GUILD_ROLE_OWNER
            )
                throw new StatusErrorException('You do not have sufficient rights to invitation personage to the guild', $this->status->main_errors);

            //TODO: Необходима проверка на уровень симпатии приглашаемого персонажа
            //Получаем данные по приглашению
            $myInvitation = guild_RequestInvitation::model()->findRequestOrInvitationOnIdGuild($idGuild, $idInvitedPersonage);

            if ($myInvitation != NULL)
                throw new StatusErrorException('The invitation already exists', $this->status->main_errors);

            //Добавляем приглашение персонажа на союз
            $doneInsertInvitation = guild_RequestInvitation::model()->insertRequestOrInvitation($idInvitedPersonage, $idGuild,
                guild_RequestInvitation::GUILD_INVITATION);

            if ($doneInsertInvitation === true) {
                $this->Viewer_Assign('status', $this->status->successfully);
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `actionApplyInvitation` validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `actionApplyInvitation` error: ', $e->getMessage());
        }
    }

    /**
     * Формируем данные всех союзов для отправки клиентской части приложения
     *
     * @param $personageGuilds
     * @return array
     * @throws StatusErrorException
     */
    private function _formedResponseResult($personageGuilds)
    {
        $formedDataGuild = array();
        foreach ($personageGuilds as $personage) {
            $formedDataGuild['nick_personage'] = $personage->nick;
            $formedDataGuild['dignity_personage'] = $personage->name_dignity;
            $formedDataGuild['name_guild'] = $personage->name;
            $formedDataGuild['id_guild'] = $personage->id;
            $formedDataGuild['experience_guild'] = $personage->experience;
            $formedDataGuild['status_guild_personage'] = ($personage->role_in_guild == '') ? '' : $personage->role_in_guild;
            $formedDataGuild['id_guild'] = $personage->guild_id;
            $formedDataGuild['id_personage'] = $personage->id_personage;
        }

        if (empty($formedDataGuild)) {
            throw new StatusErrorException('Not data personage guild', $this->status->main_errors);
        }

        return $formedDataGuild;
    }

    /**
     * Формируем данные связанные с заявками и приглашениями, для отправки клиентской части
     *
     * @param $RequestInvitation
     * @return array
     * @throws StatusErrorException
     */
    private function _formedRequestOrInvitation($RequestInvitation)
    {
        $formedDataGuild = array();
        foreach ($RequestInvitation as $personage) {
            $formedDataGuild['nick_personage'] = $personage->nick;
            $formedDataGuild['dignity_personage'] = $personage->name_dignity;
            $formedDataGuild['name_guild'] = $personage->name;
            $formedDataGuild['id_guild'] = $personage->id;
            $formedDataGuild['experience_guild'] = $personage->experience;
            $formedDataGuild['status_guild_personage'] = ($personage->role_in_guild == '') ? '' : $personage->role_in_guild;
            $formedDataGuild['status_request_invitation'] = $personage->status_request_invitation;
            $formedDataGuild['id_request_invitation'] = $personage->id_personages_guilds_request_invitation;
        }

        if (empty($formedDataGuild)) {
            throw new StatusErrorException('Not data personage request or invitation', $this->status->main_errors);
        }

        return $formedDataGuild;
    }

    /**
     * Формируем данные текущих и последующих бонусов и опытов союза
     *
     * @param $currentBonusAndExperience
     * @param $nextBonusAndExperience
     * @return array
     * @throws StatusErrorException
     */
    private function _formedResponseBonusesAndExperience($currentBonusAndExperience, $nextBonusAndExperience)
    {
        $formedBonusesAndExperienceGuild = array();
        $formedBonusesAndExperienceGuild['current_name_bonuses'] = ($currentBonusAndExperience->name_bonus != NULL) ?
            $currentBonusAndExperience->name_bonus : '';

        $formedBonusesAndExperienceGuild['next_name_bonuses'] = ($nextBonusAndExperience->name_bonus != NULL) ?
            $nextBonusAndExperience->name_bonus : '';

        $formedBonusesAndExperienceGuild['next_experience'] = $nextBonusAndExperience->experience;

        return $formedBonusesAndExperienceGuild;
    }
}
