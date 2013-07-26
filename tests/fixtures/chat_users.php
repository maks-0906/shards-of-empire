<?php

require_once ROOT . '/Application/chat/Mapper.php';

/**
 * Файл содержит фикстуры для таблицы `users_challenges`
 *
 * @author Greg
 * @package tests
 */
return array(
    'msg1' => array(
        'sender_id' => 1,
		'recipient_id' => null,
		'channel_id' => ChatMessageMapper::MAIN_TYPE_MESSAGE,
		'world_id' => 0,
		'text' => 'Test message',
		'create_time' => 'NOW()'
    ),
	'msg2' => array(
		'sender_id' => 1,
		'recipient_id' => 2,
		'channel_id' => ChatMessageMapper::TRADE_TYPE_MESSAGE,
		'world_id' => 0,
		'text' => 'Test message trade channel message from id 1 users to 2',
		'create_time' => 'NOW()'
	),
	'msg3' => array(
		'sender_id' => 2,
		'recipient_id' => 1,
		'channel_id' => ChatMessageMapper::TRADE_TYPE_MESSAGE,
		'world_id' => 0,
		'text' => 'Test message trade channel message from id 2 users to 1',
		'create_time' => 'NOW()'
	),
	'msg4' => array(
		'sender_id' => 2,
		'recipient_id' => null,
		'channel_id' => ChatMessageMapper::MAIN_TYPE_MESSAGE,
		'world_id' => 1,
		'text' => 'Test message in world id 1 main type',
		'create_time' => 'NOW()'
	),
	'msg5' => array(
		'sender_id' => 1,
		'recipient_id' => null,
		'channel_id' => ChatMessageMapper::MAIN_TYPE_MESSAGE,
		'world_id' => 1,
		'text' => 'Test message in world id 1 main type',
		'create_time' => 'NOW()'
	),
	'msg6' => array(
		'sender_id' => 1,
		'recipient_id' => 2,
		'channel_id' => ChatMessageMapper::GUILD_TYPE_MESSAGE,
		'world_id' => 0,
		'text' => 'Test message in id 1 world from id 1 user to 2 user',
		'create_time' => 'NOW()'
	),
);


