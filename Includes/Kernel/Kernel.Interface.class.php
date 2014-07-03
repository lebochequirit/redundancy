<?php
	/**
	* Kernel.Interface.class.php
	*/	
	namespace Redundancy\Kernel;
	/**
	* This class contains needed functions to deliver data to the interface
	* @license
	*
	* This program is free software; you can redistribute it and/or
	* modify it under the terms of the GNU General Public License as
	* published by the Free Software Foundation; either version 3 of
	* the License, or (at your option) any later version.
	*
	* This program is distributed in the hope that it will be useful, but
	* WITHOUT ANY WARRANTY; without even the implied warranty of
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
	* General Public License for more details at
	* http://www.gnu.org/copyleft/gpl.html
	* 
	* @author  squarerootfury <me@0fury.de>	
	* @todo DoS Attack over calling SetCurrentLanguage() ??
	* 
	*/
	class InterfaceKernel{
		/**
		* An array containg the language data
		*/
		private $Language;
		/**
		* An language code of the current language data
		*/
		private $LanguageCode;
		/**
		* Constructor
		* @param $languageCode an language code or if not set the default value from Program_Language
		*/
		public function __construct($languageCode = -1){
			if ($languageCode == -1)
				$languageCode = $GLOBALS["Kernel"]->Configuration["Program_Language"];
			if (file_exists(__REDUNDANCY_ROOT__."Language/".$languageCode.".lng")){
				$this->Language = parse_ini_file(__REDUNDANCY_ROOT__."Language/".$languageCode.".lng");	
				$this->LanguageCode = $languageCode;
			}	
		}
		/**
		* Returns an language value by a given key
		* @param $key the key of the value
		* @return string| null
		*/
		public function GetLanguageValue($key){	
			return $this->Language[$key];
		}
		/**
		* Get the complete language array
		* @return array the language array
		*/
		public function GetAllLanguageValues(){
			return $this->Language;
		}
		/**
		* Get the current language code
		* @return string the language code
		*/
		public function GetCurrentLanguage(){
			return $this->Language;
		}
		/**
		* Set the language code an reparse the language data
		* @param $languageCode the code to get parsed.
		*/
		public function SetCurrentLanguage($languageCode){
			if (file_exists(__REDUNDANCY_ROOT__."Language/".$languageCode.".lng")){
				$this->Language = $languageCode;
				$this->LanguageCode = $languageCode;
			}
		}
	}
?>