<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Utf8 extends CI_Utf8 
{
	/**
	 * Clean UTF-8 strings
	 *
	 * Ensures strings are UTF-8
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function clean_string($str)
	{
		if ($this->_is_ascii($str) === FALSE)
		{
			$str = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $str);
		}

		return $str;
	}
}

?>