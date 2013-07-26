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
		'channel_id' => chat_Mapper::MAIN_TYPE_MESSAGE,
		'world_id' => 0,
		'text' => 'Test message',
    ),
	'msg2' => array(
		'sender_id' => 1,
		'recipient_id' => 2,
		'channel_id' => chat_Mapper::TRADE_TYPE_MESSAGE,
		'world_id' => 0,
		'text' => 'Test message trade channel message from id 1 users to 2',
	),
	'msg3' => array(
		'sender_id' => 2,
		'recipient_id' => 1,
		'channel_id' => chat_Mapper::TRADE_TYPE_MESSAGE,
		'world_id' => 0,
		'text' => 'Test message trade channel message from id 2 users to 1',
	),
	'msg4' => array(
		'sender_id' => 2,
		'recipient_id' => null,
		'channel_id' => 0,
		'world_id' => 0,
		'text' => 'Test message in world id 1 main type',
	),
	'msg5' => array(
		'sender_id' => 1,
		'recipient_id' => null,
		'channel_id' => chat_Mapper::MAIN_TYPE_MESSAGE,
		'world_id' => 1,
		'text' => 'Test message in world id 1 main type',
	),
	'msg6' => array(
		'sender_id' => 1,
		'recipient_id' => 2,
		'channel_id' => chat_Mapper::MAIN_TYPE_MESSAGE,
		'world_id' => 0,
		'text' => 'Test message in id 1 world from id 1 user to 2 user',
		'status' => chat_Mapper::PRIVATE_STATUS
	),
	'msg7' => array(
		'sender_id' => 7,
		'recipient_id' => null,
		'channel_id' => 0,
		'world_id' => 0,
		'text' => 'Test message in world id 1 main type',
	),
	'msg8' => array(
		'sender_id' => 1,
		'recipient_id' => 7,
		'channel_id' => chat_Mapper::MAIN_TYPE_MESSAGE,
		'world_id' => 1,
		'text' => 'Test message in world id 1 main type',
	),
	'msg9' => array(
		'sender_id' => 7,
		'recipient_id' => 2,
		'channel_id' => chat_Mapper::MAIN_TYPE_MESSAGE,
		'world_id' => 0,
		'text' => 'Test message in id 1 world from id 1 user to 2 user',
		'status' => chat_Mapper::PRIVATE_STATUS
	),
);


