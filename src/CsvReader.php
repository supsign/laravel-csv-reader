<?php

namespace Supsign\LaravelCsvReader;

use Supsign\LaravelCsvReader\ImportHelper;

use AllowDynamicProperties;

#[AllowDynamicProperties]
class CsvReader extends ImportHelper {
	protected
		$escape 				= '\\',
		$fieldAddresses 		= [],
		$fieldAddressDelemiter 	= false,
		$lines 					= [],
		$line 					= false,
		$lineCounter			= 0,
		$lineDelimiter			= false;

	//	line functions

	protected function addLine($line) {
		$this->lines[] = $line;

		return $this;
	}

	protected function clearLines() {
		$this->lines = [];

		return $this->clearCurrentLine();
	}

	protected function clearCurrentLine() {
		$this->line = false;

		return $this;
	}

	protected function encodeValue($value) {
		return $value;
	}

	protected function getCurrentLine() {
		return $this->line;
	}

	protected function getLines() {
		return $this->lines;
	}

	protected function getLinesCount() {
		return count($this->getLines() );
	}

	protected function iterateLines() {
		if ($this->lineCounter >= $this->getLinesCount() ) {
			$this
				->resetLineCounter()
				->clearCurrentLine();

			return false;
		}
		$this->line = $this->getLines()[$this->lineCounter++];

		foreach ($this->getCurrentLine() AS $key => $value)
			$this->{lcfirst($key)} = $this->encodeValue($value);

		return true;
	}

	protected function resetLineCounter() {
		$this->lineCounter = 0;

		return $this;
	}

	//	lineDelimiter functions

	protected function getLineDelimiter() {
		return $this->lineDelimiter;
	}

	//	escape functions

	protected function getEscape() {
		return $this->escape;
	}


	//	general functions

	public function readFiles() {
		if (!empty($this->lines))
			return $this;

		if (!$this->getFileHandles() )
			$this->openFiles();

		foreach ($this->getFileHandles() AS $this->fileHandle) {
			while ($line = fgetcsv($this->fileHandle, null, $this->getLineDelimiter(), '"', $this->getEscape() ) ) {
				if (!is_array($line) )
					continue;

				if ($this->getFieldAddresses() ) {
					foreach (array_keys(array_diff_key($line, $this->getFieldAddresses() ) ) AS $field)
						unset($line[$field]);

					$line = array_combine($this->getFieldAddresses(), array_pad($line, count($this->getFieldAddresses()), ''));
				}

				$this->addLine($line);
			}
		}

		return $this->closeFileHandles();
	}

	public function readString($content) {
		$handle = fopen('php://temp', 'r+');
		fwrite($handle, $content);
		rewind($handle);

		return $this
			->clearLines()
			->addFileHandle($handle)
			->readFiles();
	}

	public function setEscape($escape) {
		$this->escape = $escape;

		return $this;
	}

	public function setLineDelimiter($delimiter) {
		$this->lineDelimiter = $delimiter;

		return $this;
	}
}