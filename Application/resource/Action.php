<?php
/**
 * Файл содержит логику обработки запросов и управление ресурсами пользователя
 *
 * @author Greg
 * @package
 */

/**
 * Description of Action
 *
 * @property Auth $oAuth
 * @property Viewer $oViewer
 * @property Config $oConfig
 * @property array $json
 * @author al
 */
class resource_Action extends JSONAction
{
    public function RegisterEvent()
    {
        $this->AddEvent('info.json', 'actionEverythingRelatedToResources', Auth::AUTH_USER);
        $this->AddEvent('properties.json', 'actionPropertiesResources', Auth::AUTH_USER);
        $this->AddEvent('only_resources.json', 'actionGetAllResources', Auth::AUTH_USER);
        $this->AddEvent('base_resources.json', 'actionGetBaseResourcesForCity', Auth::AUTH_USER);
    }

    /**
     * Предоставляем данные для модального окна ресурсов города
     *
     * @throws StatusErrorException
     */
    public function actionEverythingRelatedToResources()
    {
        try {
            fb($_SESSION, 'sess', FirePHP::ERROR);
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $resourceInfo = resource_Mapper::model()->findEverythingRelatedToResources($idPersonage);
            if ($resourceInfo == null)
                throw new StatusErrorException('No data to fill the resource modal window', $this->status->main_errors);

            $this->Viewer_Assign('info', $this->formatJSONResponse($resourceInfo));

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action actionEverythingRelatedToResources validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionEverythingRelatedToResources` error: ', $e->getMessage());
        }
    }

    /**
     * Действие даёт возможность получить все ресурсы и доступно только для текущего персонажа.
     * Иначе, если пользователь не в мире действие не будет выполнено.
     */
    public function actionGetAllResources()
    {
        try
		{
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $coordinates = Auth::getCurrentLocationCoordinates();

            /*var $city personage_City*/
            $city = personage_City::model()->findIdConcreteCityCoordinates(
                $coordinates['x'], $coordinates['y'], Auth::getIdPersonage()
            );

            if ($city == null)
                throw new StatusErrorException('Personage is not in your city', $this->status->main_errors);

            $resources = personage_ResourceState::model()->findAllResourcesWithResourceStatePersonageCity($city->id);

            if (!empty($resources)) {
                $this->Viewer_Assign('info', $this->formatJSONResponse($resources));
            } else {
                throw new StatusErrorException('No data on initial resource', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action actionGetAllResources validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionGetAllResources` error: ', $e->getMessage());
        }
    }

	/**
	 * Действие создаёт выборку по базовым ресурсам персонажа в городе.
	 *
	 * @throws StatusErrorException
	 */
	public function actionGetBaseResourcesForCity()
	{
		try
		{
			$x = $this->GetVar('x');
			$y = $this->GetVar('y');
			if($x == null)
				throw new StatusErrorException('Parameter `x` not defined', $this->status->main_errors);

			if($y == null)
				throw new StatusErrorException('Parameter `y` not defined', $this->status->main_errors);

			$idPersonage = Auth::getIdPersonage();
			if ($idPersonage == null)
				throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

			/*var $city personage_City*/
			$city = personage_City::model()->findIdConcreteCityCoordinates($x, $y, $idPersonage);
			if ($city == null)
				throw new StatusErrorException('City ​​does not belong to the personage', $this->status->main_errors);

			$resources = personage_ResourceState::model()->findBaseResourcesForCity($city->id);
			if (!empty($resources)) {
				$this->Viewer_Assign('resources', $this->formatJSONResponse($resources));
			} else {
				throw new StatusErrorException('No data on initial resource', $this->status->main_errors);
			}

		} catch (JSONResponseErrorException $e) {
			$e->sendResponse($this, 'Action actionGetBaseResourcesForCity validate: ');
		}
		catch (Exception $e) {
			e1('Action `actionGetBaseResourcesForCity` error: ', $e->getMessage());
		}
	}

    /**
     * Предосталяем данные по конкретным ресурсам
     *
     * @throws StatusErrorException
     */
    public function actionPropertiesResources()
    {
        try {
            $idResource = $this->GetVar('id_resource');

            if ($idResource == null)
                throw new StatusErrorException(
                    'Parameter `$idResource` for server not defined', $this->status->main_errors
                );

            fb($_SESSION, 'sess', FirePHP::ERROR);

            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);


            $coordinates = Auth::getCurrentLocationCoordinates();

            /*var $city personage_City*/
            $city = personage_City::model()->findIdConcreteCityCoordinates($coordinates['x'], $coordinates['y'], $idPersonage);
            $idCity = $city->id;

            if (empty($idCity)) throw new StatusErrorException('No ID city', $this->status->main_errors);

            $comparisonResources = true;
            $resourcesPersonage = $this->getResultResourcesPersonage($idResource, $idPersonage, $comparisonResources);

            if ($resourcesPersonage !== false) {
                $resourceState = $resourcesPersonage;
            }

            $resourcesBasicAndSpecial = $this->getDataOnBasicAndSpecialResources($idCity, $idResource, $idPersonage);

            if ($resourcesBasicAndSpecial !== false) {
                $resourceState = $resourcesBasicAndSpecial;
            }

            if (empty($resourceState)) throw new StatusErrorException('No data for resource', $this->status->personage_not_exists);
            $this->Viewer_Assign('properties', $this->formatJSONResponse($resourceState));

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action actionPropertiesResources validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionPropertiesResources` error: ', $e->getMessage());
        }
    }

    /**
     * Получить данные о ресурсах персонажа
     *
     * @param $idResources
     * @param $idPersonage
     * @param bool $comparisonResources
     * @return array|bool|personage_ResourceState
     */
    public function getResultResourcesPersonage($idResources, $idPersonage, $comparisonResources = false)
    {
        $resourcesPersonage = personage_ResourceState::model()->findAmountResourcesPersonage($idResources, $idPersonage, $comparisonResources);
        $factoryResource = resource_Factory::getResourceFactory($resourcesPersonage[0]->name_resource);

        if ($factoryResource != NULL) {
            $resourcesPersonage[0]->income = $factoryResource->toRaiseRevenuesForModalWindowsResources();
        }

        if (!empty($resourcesPersonage)) {
            return $resourcesPersonage;
        } else {
            return false;
        }
    }

    /**
     * Получить данные дл яосновных и сцециальных ресурсах
     *
     * @param $idCity
     * @param $idResources
     * @param $idPersonage
     * @return array|bool|personage_ResourceState
     */
    public function getDataOnBasicAndSpecialResources($idCity, $idResources, $idPersonage)
    {
        $resourcesBasicAndSpecial = personage_ResourceState::model()->findPropertiesResources($idCity, $idResources, $idPersonage);
        $building = building_Development::model()->findDataDependingOnResources($idResources, $idCity);
        $factoryResource = resource_Factory::getResourceFactory($resourcesBasicAndSpecial[0]->name_resource);

        if ($factoryResource != NULL) {
            $factoryResource->setBuilding($building);
            $factoryResource->setCity($idCity);
            $factoryResource->setNameResource($resourcesBasicAndSpecial[0]->name_resource);
            $resourcesBasicAndSpecial[0]->income = $factoryResource->toRaiseRevenuesForModalWindowsResources();
        }

        if (!empty($resourcesBasicAndSpecial)) {
            return $resourcesBasicAndSpecial;
        } else {
            return false;
        }
    }
}