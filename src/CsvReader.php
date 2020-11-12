<?php

namespace Supsign\LaravelCsvReader;

use Supsign\LaravelCsvReader\importHelper;

class CsvReader extends importHelper {
	protected
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


	//	general functions

	public function readFiles() {
		if (!$this->getFileHandles() )
			$this->openFiles();

		$this->clearLines();

		foreach ($this->getFileHandles() AS $this->fileHandle) {
			while ($line = fgetcsv($this->fileHandle, null, $this->getLineDelimiter() ) ) {
				if (!is_array($line) )
					continue;

				if ($this->getFieldAddresses() ) {
					foreach (array_keys(array_diff_key($line, $this->getFieldAddresses() ) ) AS $field)
						unset($line[$field]);

					$line = array_combine($this->getFieldAddresses(), $line);
				}

				$this->addLine($line);
			}
		}

		return $this->closeFileHandles();
	}

	public function setLineDelimiter($delimiter) {
		$this->lineDelimiter = $delimiter;

		return $this;
	}
}