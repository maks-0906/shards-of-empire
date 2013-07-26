<?php

require_once ROOT . '/Application/map/Comb.php';

/**
 * Файл содержит фикстуры для таблицы `group_cells`
 *
 * @author Greg
 * @package tests
 */

function generateCells()
{
	$cells = array();
	for($i = 0; $i < 100; $i++)
		$cells[$i] = $i;

	return implode('', $cells);
}

return array(
    'cell1' => array(
        'id_map_template' => 1,
		'levels_cells' => generateCells(),
        'id_fraction_cell' => 1,
        'max_count_personage' => 6,
        'current_count_personage' => 4,
        'count_ruins' => 15,
		'y_0' => 0,
		'x_0' => 0,
		'y_1' => 99,
        'x_1' => 99,
    ),
	'cell2' => array(
		'id_map_template' => 1,
		'levels_cells' => generateCells(),
		'id_fraction_cell' => 1,
		'max_count_personage' => 6,
		'current_count_personage' => 4,
		'count_ruins' => 15,
		'y_0' => 100,
		'x_0' => 100,
		'y_1' => 199,
		'x_1' => 199,
	),
	'cell3' => array(
		'id_map_template' => 1,
		'levels_cells' => generateCells(),
		'id_fraction_cell' => 1,
		'max_count_personage' => 6,
		'current_count_personage' => 4,
		'count_ruins' => 15,
		'y_0' => 200,
		'x_0' => 200,
		'y_1' => 299,
		'x_1' => 299,
	),
	'cell4' => array(
		'id_map_template' => 1,
		'levels_cells' => generateCells(),
		'id_fraction_cell' => 1,
		'max_count_personage' => 6,
		'current_count_personage' => 4,
		'count_ruins' => 15,
		'y_0' => 300,
		'x_0' => 300,
		'y_1' => 399,
		'x_1' => 399,
	),
	'cell5' => array(
		'id_map_template' => 1,
		'levels_cells' => generateCells(),
		'id_fraction_cell' => 1,
		'max_count_personage' => 6,
		'current_count_personage' => 4,
		'count_ruins' => 15,
		'y_0' => 400,
		'x_0' => 400,
		'y_1' => 499,
		'x_1' => 499,
	),
	'cell6' => array(
		'id_map_template' => 1,
		'levels_cells' => generateCells(),
		'id_fraction_cell' => 1,
		'max_count_personage' => 6,
		'current_count_personage' => 4,
		'count_ruins' => 15,
		'y_0' => 500,
		'x_0' => 500,
		'y_1' => 599,
		'x_1' => 599,
	),
	'cell7' => array(
		'id_map_template' => 1,
		'levels_cells' => generateCells(),
		'id_fraction_cell' => 1,
		'max_count_personage' => 6,
		'current_count_personage' => 4,
		'count_ruins' => 15,
		'y_0' => 600,
		'x_0' => 600,
		'y_1' => 699,
		'x_1' => 699,
	),
	'cell8' => array(
		'id_map_template' => 1,
		'levels_cells' => generateCells(),
		'id_fraction_cell' => 1,
		'max_count_personage' => 6,
		'current_count_personage' => 4,
		'count_ruins' => 15,
		'y_0' => 700,
		'x_0' => 700,
		'y_1' => 799,
		'x_1' => 799,
	),
	'cell9' => array(
		'id_map_template' => 1,
		'levels_cells' => generateCells(),
		'id_fraction_cell' => 1,
		'max_count_personage' => 6,
		'current_count_personage' => 4,
		'count_ruins' => 15,
		'y_0' => 800,
		'x_0' => 800,
		'y_1' => 899,
		'x_1' => 899,
	),
	'cell10' => array(
		'id_map_template' => 1,
		'levels_cells' => generateCells(),
		'id_fraction_cell' => 1,
		'max_count_personage' => 6,
		'current_count_personage' => 4,
		'count_ruins' => 15,
		'y_0' => 900,
		'x_0' => 900,
		'y_1' => 999,
		'x_1' => 999,
	),
);
