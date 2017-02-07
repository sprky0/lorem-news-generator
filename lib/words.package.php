<?php

class WordException extends Exception {}

class Words {

	/**
	 * @var array $words Map of references to all the words available.
	 */
	protected $words = array();

	/**
	 * @var array $starters Cache of references to words that are known to sometimes start a sentence.
	 */
	protected $starters = array();

	/**
	 * @var array $enders Cache of references to words that are known to sometimes start a sentence.
	 */
	protected $enders = array();

	protected $max_length_safety = 100;

	public function __tostring() {
		// print_r($this->words);
		// not sure what to do here, maybe output a book
	}

	public function getSentence($min_length = 5, $max_length = 20) {

		$sentence = array();
		$sentence[] = $this->getStartWord();

		if (!isset($sentence[0]))
			return false;

		$pointer = $sentence[0];

		for($i = 1; $i < $this->max_length_safety; $i++) {

			$next = $pointer->getRandomChild();

			if ($next == false)
				break;

			if ($i > rand($min_length,$max_length) && true == $pointer->can_end)
				break;

			$sentence[$i] = $next;
			$pointer = $next;

		}

		// $sentence[] = $this->getEndWord();

		$sentence[0] = ucfirst($sentence[0]);

		return implode(" ", $sentence) . $this->getPunctuation();
	}

	public function getPunctuation() {
		$punctuation = array(".", ".", ".", ".", ".", "!", "?", "!?");
		return $punctuation[ rand(0,count($punctuation) - 1) ];
	}

	public function addWord($word, $previous = null, $next = null) {

		$primary_reference = $this->getWord($word);

		if (null == $primary_reference) {
			$primary_reference = new Word($word);
			$this->words[$word] = &$primary_reference;
		}

		$parent_reference = $this->getWord($previous);

		if (null == $parent_reference && !empty($previous)) {
			$parent_reference = new Word($previous);
			$this->words[$previous] = &$parent_reference;
		}

		$child_reference = $this->getWord($next);

		if (null == $child_reference && !empty($next)) {
			$child_reference = new Word($next);
			$this->words[$next] = &$child_reference;
		}

		if (!empty($previous))
			$primary_reference->addParent($parent_reference);
		else {
			$primary_reference->setCanStart(true);
			$this->starters[] = &$primary_reference;
		}

		if (!empty($next))
			$primary_reference->addChild($child_reference);
		else {
			$primary_reference->setCanEnd(true);
			$this->enders[] = &$primary_reference;
		}

	}

	public function getEndWord() {
		if (count($this->enders) == 0)
			throw new WordException("missing startwords");
		return $this->enders[ rand( 0, count($this->enders) - 1 ) ];
	}

	public function getStartWord() {
		if (count($this->starters) == 0)
			throw new WordException("missing startwords");
		return $this->starters[ rand( 0, count($this->starters) - 1 ) ];
	}

	/**
	 * Get a reference to a word
	 *
	 * @param string $word
	 * @return mixed Word|null
	 */
	public function getWord($word) {
		// $word = trim(strtolower($word));
		$word = trim($word);
		if (isset($this->words[$word]))
			return $this->words[$word];

		return null;
	}

	/**
	 * Read a text file pass it to the parser
	 *
	 * @param string $file Location of the text file that will be loaded and parsed.
	 */
	public function parseFile($file) {
		$data = file_get_contents($file);
		return $this->parse($data);
	}

	/**
	 * Sanitize string input and pass it to the parser
	 *
	 * @param string $string String that will be parsed
	 */
	public function parseString($string) {
		return $this->parse($string);
	}

	/**
	 *
	 */
	protected function parse($data) {

		$para_count = 0;
		$sent_count = 0;
		$word_count = 0;

		$data = str_replace("\n\r", "\n", $data);
		$data = str_replace("\r\n", "\n", $data);

		$paragraphs = explode("\n\n", $data);

		$stdOut = fopen('php://stdout', 'w');
		fwrite($stdOut, "\noh hey, got some shit to parse\n");


		// or do this ?
		// $parts = preg_split('/\s+/', $str);

		foreach($paragraphs as $paragraph) {

			$sentences = preg_split("/\.|!|\?/", $paragraph);

			foreach($sentences as $sentence) {

				$words = preg_split("/ |,/", $sentence);
				$max = count($words);
				$previous = null;

				for($i = 0; $i < $max; $i++) {

					// $word = trim($words[$i]);
					$word = $words[$i];

					$word = html_entity_decode($word);

					$word = preg_replace("/[^A-Za-z0-9 ]/", '', $word);
					fwrite($stdOut, $word . "|");

					/*
					foreach(["\"","(",")",";",":","&raquo;","&laquo;","[","]","{","}","-","  "," "] as $charToRemove) {
						$word = str_replace($charToRemove,"",$word);
					}
					*/

					if (empty($word))
						continue;

					/*
					if (in_array($word, ["-","_",":",";"]))
						continue;

					if (is_numeric($word))
						continue;
					*/

					$previous = isset($words[$i - 1]) ? $words[$i - 1] : null;
					$next = isset($words[$i + 1]) ? $words[$i + 1] : null;

					$this->addWord($word, $previous, $next);
					$word_count++;
				}

				unset($sentences);
				$sent_count++;
			}
			$para_count++;
		}

		unset($paragraphs);
		unset($data);

		// results!

		return array(
			"paragraphs" => $para_count,
			"sentences" => $sent_count,
			"words" => $word_count
		);

	}

}

class Word {

	public $word;
	public $parents = array();
	public $children = array();

	public $can_start = false;
	public $can_end = false;

	public function __construct($word) {
		// $this->word = strtolower($word);
		// $this->word = $word;
		$this->word = $this->sanitize($word);
	}

	public function __toString() {
		return $this->word;
	}

	public function addChild(Word $word) {
		$this->children[] = &$word;
		return $this;
	}

	public function addParent(Word $word) {
		$this->parents[] = &$word;
		return $this;
	}

	public function getRandomChild() {

		if (count($this->children) == 0)
			return false;

		$child_count = count($this->children);
		$index = rand(0,$child_count - 1);
		return $this->children[$index];
	}

	public function getRandomParent() {

		if (count($this->parents) == 0)
			return false;

		$parent_count = count($this->parents);
		$index = rand(0,$parent_count - 1);
		return $this->parents[$index];
	}

	public function getParent($index) {
		return $this->parents[$index];
	}

	public function setCanEnd($endability) {
		$this->can_end = (boolean) $endability;
	}

	public function setCanStart($startability) {
		$this->can_start = (boolean) $startability;
	}

	private function sanitize($string) {
		// could jam this crap in here
		return $string;
	}

}
