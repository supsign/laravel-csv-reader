<?php

namespace Supsign\LaravelCsvReader;

class DirectoryReader {
	protected 
		$directories		= [],
		$directory      	= false,
		$directoryHandles 	= [],
		$directoryHandle    = false,
		$files 				= [],
		$file				= false,
		$fileExtensions 	= [],
		$fileHandles    	= [],
		$fileHandle     	= false,
		$fileNames      	= [];

	//	Directory functions

	public function addDirectory($dir) {
		$this->directories[] = $this->validateDirectory($dir);

		return $this;
	}

	public function clearDirectories() {
		$this->directories = [];
		$this->directory   = false;

		return $this;
	}

	protected function getCurrentDirectory() {
		return $this->directory;
	}

	public function getDirectories() {
		return $this->directories;
	}

	public function readDirectories() {
		$this->clearFiles();

		if (!$this->getDirectories() )
			return $this;

		foreach ($this->getDirectories() AS $this->directory)
			$this->addDirectoryHandle(opendir($this->getCurrentDirectory() ) );

		foreach ($this->getDirectoryHandles() AS $this->directory => $this->directoryHandle) {
			while ( ($file = readdir($this->getCurrentDirectoryHandle() ) ) !== false) {
				if (filetype($this->getCurrentDirectory().$file) != 'file')
					continue;

				if ($this->getFileExtensions() AND !in_array($this->getFileExtension($file), $this->getFileExtensions() ) )
					continue;

				if ($this->getFileNames() AND !$this->inFileNames($file) )
					continue;

				$this->addFile($file);
			}
		}

		return $this;
	}

	public function setDirectory($dir) {
		return $this->clearDirectories()->addDirectory($dir);
	}

	public function setDirectories(array $dirs) {
		$this->clearDirectories();

		foreach ($dirs AS $dir)
			$this->addDirectory($dir);

		return $this;
	}

	public static function validateDirectory($dir) {
		if (!empty($dir) AND is_string($dir) AND file_exists($dir) )
			return substr($dir, -1) == '/' ? $dir : $dir.'/';

		throw new Exception('"'.$dir.'" is not a valid directory');
	}

	//	directoryHandle functions 

	protected function addDirectoryHandle($handle) {
		$this->directoryHandles[$this->getCurrentDirectory()] = $this->validateDirectoryHandle($handle);

		return $this;
	}

	protected function clearDirectoryHandles() {
		$this->directoryHandles = [];
		$this->directoryHandle  = false;

		return $this;
	}

	protected function closeDirectoryHandles() {
		foreach ($this->getDirectoryHandles() AS $this->directoryHandle)
			fclose($this->directoryHandle);

		return $this->clearDirectoryHandles();
	}

	protected function getDirectoryHandles() {
		return $this->directoryHandles;
	}

	protected function getCurrentDirectoryHandle() {
		return $this->directoryHandle;
	}

	protected function validateDirectoryHandle($handle) {
		if (get_resource_type($handle)  == 'stream')
			return $handle;

		throw new Exception('invalid directory handle');
	}


	//	File functions

	protected function addFile($file) {
		$this->files[$this->getCurrentDirectory()][] = $this->validateFile($file);

		return $this;
	}

	protected function clearFiles() {
		$this->files = [];
		$this->file  = false;

		return $this;
	}

	protected function getCurrentFile() {
		return $this->file;
	}

	protected function getCurrentFileExtension() {
		return $this->getFileExtension($this->getCurrentFile() );
	}

	protected function getCurrentFileName() {
		return $this->fileName;
	}

	public function getFiles() {
		return $this->files;
	}

	public function openFiles() {
		if (!$this->getFiles() )
			$this->readDirectories();

		$this->clearFileHandles();

		foreach ($this->getFiles() AS $this->directory => $files)
			foreach ($files AS $this->file) {
				if ( ($this->fileHandle = fopen($this->getCurrentDirectory().$this->getCurrentFile(), "r") ) === false)
					continue;

				$this->addFileHandle($this->fileHandle);
			}

		// return $this->closeDirectoryHandles();
	}

	protected function validateFile($file) {
		if (is_string($file) AND file_exists($this->getCurrentDirectory().$file) )
			return $file;

		throw new Exception($file.' is not a valid file name name or doesn\'t exists');
	}


	//	FileExtension functions

	public function addFileExtension($extension) {
		$this->fileExtensions[] = str_replace('.', '', $this->validateFileExtension($extension) );

		return $this;
	}

	protected function clearFileExtensions() {
		$this->fileExtensions = [];

		return $this;
	}

	public function getFileExtension($file) {
		$tmp = explode('.', $file);

		return array_pop($tmp);		//	Strict Standards: Only variables should be passed by reference
	}

	public function getFileExtensions() {
		return $this->fileExtensions;
	}

	public function setFileExtension($extension) {
		return $this->clearFileExtensions()->addFileExtension($extension);
	}

	public function setFileExtensions(array $extensions) {
		$this->clearFileExtensions();

		foreach ($extensions AS $extension)
			$this->addFileExtension($extension);

		return $this;
	}

	protected function validateFileExtension($fileExtension) {
		if (is_string($fileExtension) )
			return $fileExtension;

		throw new Exception('file name needs to be a string');
	}


	//	FileHandle functions

	protected function addFileHandle($handle) {
		$this->fileHandles[] = $this->validateFileHandle($handle);

		return $this;
	}

	protected function clearFileHandles() {
		$this->fileHandles = [];
		$this->fileHandle  = false;

		return $this;
	}

	protected function closeFileHandles() {
		foreach ($this->getFileHandles() AS $this->fileHandle)
			fclose($this->fileHandle);

		return $this->clearFileHandles();
	}

	protected function getFileHandles() {
		return $this->fileHandles;
	}

	protected function validateFileHandle($handle) {
		if (get_resource_type($handle)  == 'stream')
			return $handle;

		throw new Exception('invalid file handle');
	}


	//	FileName functions

	public function addFileName($name) {
		$this->fileNames[] = $this->validateFileName($name);

		return $this;
	}

	protected function clearFileNames() {
		$this->fileNames = [];

		return $this;
	}

	protected function getFileNames() {
		return $this->fileNames;
	}

	protected function inFileNames($file) {
		foreach ($this->getFileNames() AS $fileName)
			if (strpos($file, $fileName) !== false)
				return true;

		return false;
	}

	public function setFileName($name) {
		return $this->clearFileNames()->addFileName($name);
	}

	public function setFileNames(array $names) {
		foreach ($names AS $name)
			$this->addFileName($name);

		return $this;
	}

	protected function validateFileName($fileName) {
		if (is_string($fileName) )
			return $fileName;

		throw new Exception('file name needs to be a string');
	}
}