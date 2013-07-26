<?php
/**
 * Description content file
 *
 * @author Greg
 * @package
 */

$sql = "
--
-- Структура таблицы `chat_messages`
--

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `channel_id` int(11) NOT NULL,
  `world_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `status` enum('normal','private') NOT NULL DEFAULT 'normal',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";