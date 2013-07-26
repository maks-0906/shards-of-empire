<?php
/**
 * Файл содержит логику для завершения исследования после окончания временной метки.
 * Логика является подстраховкой, если с клиента не был послан запрос для завершения изучения исследования по кнопке
 * "Изучить".
 *
 * @author Greg
 * @package cron
 */

include_once('../bootstrap.php');

try {
    // Поиск исследований, которые ещё в процессе изучения (status='research'),
    // но с просроченной временной меткой окончания изучения.
    $research = personage_ResearchState::model()->findResearchWithFinishTime();

    //TODO: Необходима решение об кешировании одного и того же уровня
    $researchUpgrade = research_ResearchUpgrade::model()->findMaxLevelBuildingOnUpgrade();

    if (empty($research) OR empty($researchUpgrade)) {
        exit();
    }

    /* @var $r personage_ResearchState */
    foreach ($research as $r) {
        $isFinishResearch = $r->finishResearchById($r->id_personages_research_state,
                                                   $r->current_level, $researchUpgrade->max_level_for_upgrade);

        if ($isFinishResearch == false)
            e1("Не завершилось исследование с `current_research_id` (CRON): " . $r->current_research_id);
        else
            e1("Завершилось исследование с `current_research_id` (CRON): " . $r->current_research_id);
    }
} catch (Exception $e) {
    e1("Process research finish (CRON): ", $e->getMessage());
}