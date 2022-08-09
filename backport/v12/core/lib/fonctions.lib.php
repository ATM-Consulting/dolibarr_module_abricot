<?php

if ((float) DOL_VERSION < 12) {

	/**
	 * Return the value of token currently saved into session with name 'newtoken'.
	 * This token must be send by any POST as it will be used by next page for comparison with value in session.
	 *
	 * @return  string
	 */
	function newToken()
	{
		return $_SESSION['newtoken'];
	}

}
