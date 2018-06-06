<?php
class TextProcessor {
	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public function process($text) {
		$text = $this->decode($text);
		return $text;
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public function decode($text) {
		return htmlspecialchars_decode(html_entity_decode($text));
	}

}

?>