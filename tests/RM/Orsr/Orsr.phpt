<?php

use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

$orsr = new RM\Orsr\Orsr;

$nds = [
	'name' => 'Národná diaľničná spoločnosť, a.s.',
	'address' => [
		'street' => 'Dúbravská cesta',
		'number' => '14',
		'city' => 'Bratislava',
		'zip' => '84104',
	],
	'id' => '35919001',
];

$google = [
	'name' => 'Google Slovakia, s. r. o.',
	'address' => [
		'street' => 'Karadžičova',
		'number' => '8/A',
		'city' => 'Bratislava',
		'zip' => '82108',
	],
	'id' => '45947597',
];

Assert::same($nds, $orsr->getById('35919001'));

Assert::same($nds, $orsr->getById('35 919 001'));

Assert::same(NULL, $orsr->getById('35 919 00'));

Assert::same($google, $orsr->getById('45947597'));


Assert::same([$nds], $orsr->getByName('Národná diaľničná spoločnosť, a.s.'));

Assert::same([$nds], $orsr->getByName('Národná diaľničná spoločn'));

Assert::same([], $orsr->getByName('unknownCompany'));

Assert::same([$google], $orsr->getByName('google'));

$orsr->limit = 2;
Assert::same(2, count($orsr->getByName('a')));

Assert::same(10, count($orsr->getByName('a', 10, $orsr::ONLY_NAMES)));
