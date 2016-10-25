<?php

namespace RM\Orsr;

use Nette;
use Nette\Utils\Strings;

class Orsr extends Nette\Object
{
	const ONLY_NAMES = TRUE;

	public $limit = 2;

	private $url = 'http://orsr.sk/';

	public function getById($ico)
	{
		if (preg_match_all(
			"/<a href=\"([^\"]*)\" class=\"link\">Aktuálny<\/a>/",
			$this->toUtf8(file_get_contents($this->url . 'hladaj_ico.asp?ICO=' . urlencode($this->to1250($ico)) . '&SID=0')),
			$links
		) < 1)
			return;

		foreach($links[1] as $link) {
			$out = $this->parse($link);
			if ($out)
				return $out;
		}
	}

	public function getByName($name, $limit = NULL, $onlyNames = FALSE)
	{
		if ($limit === NULL) {
			$limit = $this->limit;
		}

		$tmp = [];
		if (preg_match_all(
			"/<div class=\"sbj\">(?<name>[^<]*)<\/div><\/td>\s*<td><div class=\"bmk\">\s*<a href=\"(?<url>[^\"]*)\" class=\"link\">Aktuálny<\/a>/",
			$this->toUtf8(file_get_contents($this->url . 'hladaj_subjekt.asp?OBMENO=' . urlencode($this->to1250($name)) . '&PF=0&SID=0&R=on')),
			$links
		) > 0) {
			$i=1;
			foreach($links['url'] as $link) {
				if ($onlyNames) {
					$tmp[] = [
						'name' => html_entity_decode($links['name'][$i - 1]),
					];
				} else {
					$out = $this->parse($link);
					if ($out)
						$tmp[] = $out;
				}
				$i++;
				if ($i > $limit)
					break;
			}
		}

		return $tmp;
	}

	private function parse($link) {
		$data = $this->toUtf8(file_get_contents(str_replace("&amp;", "&", $this->url . $link)));

		if (preg_match_all("/<div class=\"wrn2\">.*<\/div>/", $data, $match) !== 0)
			return FALSE;

		preg_match_all("/<td align=\"left\" valign=\"top\" width=\"20%\"> <span class=\"tl\">Obchodné meno:&nbsp;<\/span><\/td>\s*<td align=\"left\" width=\"80%\"><table width=\"100%\" border=\"0\">\s*<tr>\s*<td width=\"67%\"> <span class='ra'>(?<name>[^<]*)<\/span><br><\/td>\s*<td width=\"33%\" valign='top'>&nbsp; <span class='ra'>\(od: \d{2}\.\d{2}\.\d{4}\)<\/span><\/td>\s*<\/tr>\s*<\/table><\/td>\s*<\/tr>\s*<\/table>\s*<table width=\"100%\" border=\"0\" align=\"center\" cellspacing=\"3\" cellpadding=\"0\" bgcolor='#ffffff'>\s*<tr>\s*<td align=\"left\" valign=\"top\" width=\"20%\"> <span class=\"tl\">Sídlo:&nbsp;<\/span><\/td>\s*<td align=\"left\" width=\"80%\"><table width=\"100%\" border=\"0\">\s*<tr>\s*<td width=\"67%\"> <span class='ra'>(?<street>[^<]*)<\/span> <span class='ra'>(?<number>[^<]*)<\/span><br> <span class='ra'>(?<city>[^<]*)<\/span> <span class='ra'>(?<zip>[^<]*)<\/span><br><\/td>\s*<td width=\"33%\" valign='top'>&nbsp; <span class='ra'>\(od: \d{2}\.\d{2}\.\d{4}\)<\/span><\/td>\s*<\/tr>\s*<\/table><\/td>\s*<\/tr>\s*<\/table>\s*<table width=\"100%\" border=\"0\" align=\"center\" cellspacing=\"3\" cellpadding=\"0\" bgcolor='#ffffff'>\s*<tr>\s*<td align=\"left\" valign=\"top\" width=\"20%\"> <span class=\"tl\">IČO:&nbsp;<\/span><\/td>\s*<td align=\"left\" width=\"80%\"><table width=\"100%\" border=\"0\">\s*<tr>\s*<td width=\"67%\"> <span class='ra'>(?<id>[^<]*)<\/span><br><\/td>/i", $data, $tmp);

		return [
			'name' => Strings::trim(html_entity_decode(@$tmp['name'][0])),
			'address' => [
				'street' => Strings::trim(@$tmp['street'][0]),
				'number' => Strings::trim(@$tmp['number'][0]),
				'city' => Strings::trim(@$tmp['city'][0]),
				'zip' => Strings::trim(str_replace(' ', '', @$tmp['zip'][0])),
			],
			'id' => Strings::trim(str_replace(' ', '', @$tmp['id'][0])),
		];
	}

	private function toUtf8($string)
	{
		return iconv('windows-1250', 'utf-8', $string);
	}

	private function to1250($string)
	{
		return iconv('utf-8', 'windows-1250', $string);
	}
}
