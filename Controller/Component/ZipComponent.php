<?php
/*********************************************************************************************//**
	@file ZipComponent.php

	@brief

	Part of plugin `JDWordpress`

	@details

	This component provides basic zip file functionality to JDWordpress.

    @date           2014-02-12
    @author         Unattributed
    @copyright      Public Domain

 *************************************************************************************************/

/// Provides bacic zip file functionality to JDWordpress.
class ZipComponent extends Component
{

	var $controller;
	var $zip;


/********************************************************************//**
	@brief	Constructor

	@param	$collection		Inherited from super.
	@param	$settings		Inherited from super.
 ********************************************************************/
public function __construct(ComponentCollection $collection, $settings = array())
{
	parent::__construct($collection, $settings);
	$this->zip = new ZipArchive();
}


/********************************************************************//**
	@brief	Implements a built-in zip function.

	@param	$function		The function to call.
	@returns Result.
 ********************************************************************/
public function __get($function)
{
	return $this->zip->{$function};
}


/********************************************************************//**
	@brief	Adds to a zip file.

	@param	String 	$file		file to be included (full path).
	@param	String	$localFile	name of file in zip, if different.
	@returns Boolean
 ********************************************************************/
public function addFile($file, $localFile = null )
{
	echo $file . '<br>';
	return $this->zip->addFile($file, (is_null($localFile) ? $file : $localFile));
}


/********************************************************************//**
	@brief	Starts a access to a zip file.

	@param	String 	$path		Local path for zip.
	@param	Boolean	$overwrite	Overwrite existing file if present?
	@returns Boolean
 ********************************************************************/
public function begin($path = '', $overwrite = true)
{
	$overwrite = ($overwrite) ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE;
	return $this->zip->open($path, $overwrite);
}


/********************************************************************//**
	@brief	Closes a zip file.

	@returns Boolean
 ********************************************************************/
public function close()
{
	return $this->zip->close();
}


/********************************************************************//**
	@brief	Closes a zip file. Synonym for `close`.

	@returns Boolean
 ********************************************************************/
public function end()
{
	return $this->close();
}


/********************************************************************//**
	@brief	Adds to an open zip file via contents.

	@details ### Usage:
		`$this->Zip->addByContents('myTextFile.txt', 'Test text file');`

	@param	String 	$localFile	Local path for zip.
	@param	String	$contents	Overwrite existing file if present?
	@returns Boolean
 ********************************************************************/
public function addByContent($localFile, $contents)
{
	return $this->zip->addFromString($localFile, $contents);
}


/********************************************************************//**
	@brief	Adds entire directory to an open zip file.

	@param	String 	$directory
	@param	String	$as
	@returns Boolean
 ********************************************************************/
public function addDirectory($directory, $as)
{
	if(substr($directory, -1, 1) != DS){
		$directory = $directory.DS;
	}
	if(substr($as, -1, 1) != DS){
		$as = $as.DS;
	}
	if(is_dir($directory)){
		if($handle = opendir($directory)){
			while(false !== ($file = readdir($handle))){
				if(is_dir($directory.$file.DS)){
					if($file != '.' && $file != '..'){
						//$this->addFile($directory.$file, $as.$file);
						$this->addDirectory($directory.$file.DS, $as.$file.DS);
					}
				}else{
					$this->addFile($directory.$file, $as.$file);
				}
			}
			closedir($handle);
		}else{
			return false;
		}
	}else{
		return false;
	}
	return true;
}


/********************************************************************//**
	@brief	undo changes to an archive by index(int), name(string),
			all ('all' | '*' | blank)

	@details	### Usage:

		$this->Zip->undo(1);
		$this->Zip->undo('myText.txt');
		$this->Zip->undo('*');
		$this->Zip->undo('myText.txt, myText1.txt');
		$this->Zip->undo(array(1, 'myText.txt'));

	@param		$mixed
	@returns 	Boolean
 ********************************************************************/
public function undo($mixed = '*')
{
	if(is_array($mixed)){
		foreach($mixed as $value){
			$constant = is_string($value) ? 'Name' : 'Index';
			if(!$this->zip->unchange{$constant}($value)){
				return false;
			}
		}
	}else{
		$mixed = explode(',', $mixed);
		if(in_array($mixed[0], array('*', 'all'))){
			if(!$this->zip->unchangeAll()){
				return false;
			}
		}else{
			foreach($mixed as $name){
				if(!$this->zip->unchangeName($name)){
					return false;
				}
			}
		}
	}
	return true;
}


/********************************************************************//**
	@brief	Renames an entry in the zip file.

	@param	String 	$old
	@param	String	$new
	@returns Boolean
 ********************************************************************/
public function rename($old, $new = null)
{
	if(is_array($old)){
		foreach($old as $cur => $new){
			$constant = is_string($cur) ? 'Name' : 'Index';
			if(!$this->zip->rename{$constant}($ur, $new)){
			   return false;
			}
		}
	}else{
		$constant = is_string($old) ? 'Name' : 'Index';
		if(!$this->zip->rename{$constant}($old, $new)){
		   return false;
		}
	}

	return true;
}


/********************************************************************//**
	@brief	Finds an entry in an open zip file.

	@param			$mixed
	@param	String	$options	`FL_NODIR`, `FL_NOCASE`
	@returns index, name, or false
 ********************************************************************/
public function find($mixed, $options = 0)
{
	if(is_string($mixed)){
		return $this->zip->locatename($mixed, $options);
	}else{
		return $this->zip->getNameIndex($mixed);
	}
}


/********************************************************************//**
	@brief	Deletes an entry in an open zip file.

	@param			$mixed	delete by index(int), name(string),
							all ('all' | '*' | blank)
	@returns boolean
 ********************************************************************/
public function delete($mixed)
{
	if(is_array($mixed)){
		foreach($mixed as $value){
			$constant = is_string($value) ? 'Name' : 'Index';
			if(!$this->zip->delete{$constant}($value)){
				return false;
			}
		}
	}else{
		$mixed = explode(',', $mixed);
		foreach($mixed as $value){
			$constant = is_string($value) ? 'Name' : 'Index';
			if(!$this->zip->delete{$constant}($value)){
				return false;
			}
		}
	}
}


/********************************************************************//**
	@brief	Adds a comment to open zip file.

	@param			$mixed	comment by index(int), name(string),
							all ('all' | '*' | blank)
	@param 	String	$comment
	@returns boolean
 ********************************************************************/
public function comment($mixed = 'archive', $comment)
{
	if(is_array($mixed)){
		//unsupported currently
	}else{
		if(low($mixed) === 'archive'){
			return $this->zip->setArchiveComment($comment);
		}else{
			$constant = is_string($mixed) ? 'Name' : 'Index';
			return $this->zip->setComment{$constant}($comment);
		}
	}
}


/********************************************************************//**
	@brief	Retrieves statistics from an open zip file.

	@param			$mixed	index(int) or name(string)
	@returns stat
 ********************************************************************/
public function stats($mixed)
{
	$constant = is_string($mixed) ? 'Name' : 'Index';
	return $this->zip->stat{$constant}();
}


/********************************************************************//**
	@brief	Extracts from a zip file.

	@param	String	$location	Output location
	@param			$entries	index(int) or name(string)
	@returns stat
 ********************************************************************/
public function extract($location, $entries = null)
{
	return $this->zip->extract($location, $entries);
}


/********************************************************************//**
	@brief	Extracts from a zip file.

	@param	String	$location	Output location
	@param			$entries	index(int) or name(string)
 ********************************************************************/
public function unzip($location, $entries = null)
{
	$this->extract($location, $entries);
}

} // class
