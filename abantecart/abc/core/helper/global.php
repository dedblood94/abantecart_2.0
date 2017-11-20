<?php
/**
 * GLOBAL FUNCTIONS THAT USES A LOT INSIDE of TPL-FILES
 *
 */

/**
 * @param string $url
 * @return bool
 */
function abc_redirect($url){
	if (!$url) {
		return false;
	}
	header('Location: ' . str_replace('&amp;', '&', $url));
	exit;
}

/**
 * Echo js_encode string;
 *
 * @param string $text
 * @void
 */
function abc_js_echo($text){
	echo abc_js_encode($text);
}

/**
 * Quotes encode a string for javascript using json_encode();
 *
 * @param string $text
 * @return string
 */
function abc_js_encode($text){
	return json_encode($text, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
}

/**
 * Function output string with html-entities
 *
 * @param string $html
 */
function abc_echo_html2view($html){
	echo htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
}