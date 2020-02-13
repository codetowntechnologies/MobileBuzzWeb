<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

//This function is used to encrypt data.

if (!function_exists('simple_encrypt'))
{
	function simple_encrypt($text, $salt = "offerbabaopm.com")
	{
		return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
	}
}
// This function will be used to decrypt data.

if (!function_exists('simple_decrypt'))
{
	function simple_decrypt($text, $salt = "offerbabaopm.com")
	{
		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
	}
}