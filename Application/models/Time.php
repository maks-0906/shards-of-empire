<?php
/**
 * Класс содержит логику подсчетов связанных со временем
 */
class models_Time
{
    /**
     * Количество секунд в минуте
     */
    const SECONDS_IN_MINUTE = 60;
    const NO_VALUE = 0;

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return models_Time
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }


    /**
     * Получить количество секунд в минутах
     *
     * @param $minutes
     * @return mixed
     */
    public function getCountNumberOfSecondsInMinute($minutes)
    {
        return $minutes * self::SECONDS_IN_MINUTE;
    }

    /**
     * Подсчитать остаток времени в секундах
     *
     * @param $totalSeconds
     * @return mixed
     */
    public function calculateRemainingTimeInSeconds($totalSeconds)
    {
        $remainingTime = $totalSeconds - time();

        if ($remainingTime <= self::NO_VALUE) {
            return self::NO_VALUE;
        } else {
            return $remainingTime;
        }
    }

    /**
     * Получить временную метку окончания продления времени по минутам
     *
     * @param $minute
     * @return int
     */
    public function getTimestampProlongedByMinute($minute)
    {
        return mktime(date("H"), date("i") + $minute, date("s"), date("m"), date("d"), date("Y"));
    }

    /**
     * Получить временную метку окончания продления времени по секундам
     *
     * @param $seconds
     * @return int
     */
    public function getTimestampProlongedBySeconds($seconds)
    {
        return mktime(date("H"), date("i"), date("s") + $seconds, date("m"), date("d"), date("Y"));
    }


    /**
     * Получить текущую временную метку
     * @return int
     */
    public function getCurrentTimestamp()
    {
        return mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
    }


    /**
     * Конвертировать временную метку в дату и время
     *
     * @param $timestamp
     * @return string
     */
    public function convertTimestampToDateAndTime($timestamp)
    {
        return date('Y-m-d H:i:s', $timestamp);
    }

    /*
        public function convertDateTimeInTimestamp($dateTime)
        {
            if (!empty($dateTime)) {
                $patternDate = "/(\d+)-(\d+)-(\d+)/i";
                preg_match($patternDate, $dateTime, $matchesDate);

                $patternTime = "/(\d+):(\d+):(\d+)/i";
                preg_match($patternTime, $dateTime, $matchesTime);

                $year = $matchesDate[1];
                $month = $matchesDate[2];
                $day = $matchesDate[3];

                $hour = $matchesTime[1];
                $minutes = $matchesTime[2];
                $seconds = $matchesTime[3];

               return mktime($hour, $minutes, $seconds, $month, $day, $year);
            }

            return false;
        }
    */
    /**
     * Получить сформированную текущую дату и время
     *
     * @return string
     */
    public function getCurrentFormedDateAndTime()
    {
        return date('Y-m-d H:i:s');
    }

}
