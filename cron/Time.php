<?php
/**
 * Класс содержит методы для работы с датой и временем
 */
class Time
{
    /**
     * Текущая временная метка
     *
     * @return int
     */
    public function getCurrentTimestamp()
    {
        return mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
    }

    /**
     * Получаем текущую дату и время
     * @return string
     */
    public function getCurrentTimeDate(){
        return date("Y-m-d H:i:s");
    }
}
