<?php

namespace Supsign\LaravelCsvReader;

use Supsign\LaravelCsvReader\DirectoryReader;

class ImportHelper extends DirectoryReader {
	protected 
		$fieldAddresses   	   = [],			//	Map of Fieldnames
		$fieldAddressDelemiter = false, 		//	Delemiter for local DB address, i.e	":" in address string "Contacts:Name"
		$time 			  	   = false;

	public function __construct() {
		return $this->initImporterConfiguration();
	}

	//	local db address functions

	private function getAddressPart($part, $address) {
		if (strpos($address, $this->fieldAddressDelemiter) === false)
			return $address;

		return explode($this->fieldAddressDelemiter, $address)[$part];
	}

	protected function getColumn($address) {
		return $this->getAddressPart(1, $address);
	}

	protected function getFieldAddresses() {
		ksort($this->fieldAddresses);

		return $this->fieldAddresses;
	}

	protected function getTable($address) {
		return $this->getAddressPart(0, $address);
	}


	//	ImporterConfiguration functions

	public function getDirectories() {	//	for backwards compatibility
		if (!is_array($this->directories) AND $this->directories) {
			$dir = $this->directories;

			$this->clearDirectories()->addDirectory($dir);
		}

		return $this->directories;
	}

	protected function getFileNames() {
		if (!is_array($this->fileNames) AND $this->fileNames) {
			$name = $this->fileNames;

			$this->clearFileNames()->addFileName($name);
		}

		return $this->fileNames;
	}

	//	general functions

	public function getTime() {
		if (!$this->time)
			$this->time = new DateTime('now', new DateTimeZone('Europe/Zurich') );

		return $this->time;
	}

	public function setTime($time) {
		$this->time = strtotime($time);

		return $this;
	}

    protected function validateDate($date) {
        $parts = explode('.', $date);

        return checkdate($parts[1], $parts[0], $parts[2]);
    }

	protected static function validatePdf($filename) {
		if (is_string($filename) AND file_exists($filename) ) {
			$filename 	= fopen($filename, 'r');
			$opened = true;
		}
		if (!is_resource($filename) )
			return false;

		fseek($filename, 0);
		$data = fread($filename, 5);

		if ($opened)
			fclose($filename);

		return strcmp($data, "%PDF-") == 0;
	}
}