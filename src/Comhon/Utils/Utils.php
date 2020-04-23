<?php

/*
 * This file is part of the Comhon package.
 *
 * (c) Jean-Philippe <jeanphilippe.perrotton@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Comhon\Utils;

class Utils {
	
	/**
	 * merge arrays
	 * 
	 * keep numeric keys even if all keys are numeric 
	 * (native function array_merge transform them to have a non assoc array (0,1,2,3...))
	 * 
	 * @param array $orginalArray
	 * @param array $arrayToMerge
	 * @return array
	 */
	public static function array_merge($orginalArray, $arrayToMerge) {
		foreach ($arrayToMerge as $key => $Value) {
			$orginalArray[$key] = $Value;
		}
		return $orginalArray;
	}
	
	/**
	 * delete directory and its content from filesystem
	 * 
	 * @param string $dir
	 * @return boolean true if success
	 */
	public static function deleteDirectory($dir) {
		$files = array_diff(scandir($dir), ['.','..']);
		foreach ($files as $file) {
			is_dir($dir . DIRECTORY_SEPARATOR . $file) 
				? self::deleteDirectory($dir . DIRECTORY_SEPARATOR. $file) 
				: unlink($dir . DIRECTORY_SEPARATOR. $file);
		}
		return rmdir($dir);
	}
	
	/**
	 * 
	 * @param string $src
	 * @param string $dst
	 */
	public static function copyDirectory($src, $dst) {
		$dir = opendir($src);
		mkdir($dst);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . DIRECTORY_SEPARATOR . $file) ) {
					self::copyDirectory($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
				}
				else {
					copy($src . DIRECTORY_SEPARATOR . $file,$dst . DIRECTORY_SEPARATOR . $file);
				}
			}
		}
		closedir($dir);
	} 
	
	/**
	 * print light backtrace with only file, function and line informations
	 */
	public static function printStack() {
        $nodes = debug_backtrace();
        for ($i = 1; $i < count($nodes); $i++) {
        	var_dump("$i. ".basename($nodes[$i]['file']) .' : ' .$nodes[$i]['function'] .'(' .$nodes[$i]['line'].')');
        }
	}
	
	/**
	 * make provided string compliant with camel case
	 *
	 * @param string $string
	 * @return string
	 */
	public static function toCamelCase($string) {
		$string = preg_replace_callback(
				"|([_-][a-z])|",
				function ($matches) {return strtoupper(substr($matches[1], 1));},
				$string
				);
		return lcfirst($string);
	}
	
    /**
	 * make provided string compliant with pascal case
     *
     * @param string $string
     * @return string
     */
    public static function toPascalCase($string) {
    	return ucfirst(self::toCamelCase($string));
    }
    
    /**
	 * make provided string compliant with snake case
     *
     * @param string $string
     * @return string
     */
    public static function toSnakeCase($string) {
    	$string = lcfirst($string);
    	$string = preg_replace_callback(
    			"|(?:[^A-Z]([A-Z]))|",
    			function ($matches) {return strtolower(substr($matches[0], 0, 1) . '_' . $matches[1]);},
    			$string
    			);
    	$string = preg_replace_callback(
    			"|(?:[A-Z]([^A-Z]))|",
    			function ($matches) {return strtolower(substr($matches[0], 0, 1) . '_' . $matches[1]);},
    			$string
    			);
    	
    	return strtolower(str_replace('-', '_', $string));
    }
    
    /**
	 * make provided string compliant with kebab case
     *
     * @param string $string
     * @return string
     */
    public static function toKebabCase($string) {
    	return str_replace('_', '-', self::toSnakeCase($string));
    }
    
}
