<?php


if ((float) DOL_VERSION < 12) {

	// Apparemment il y a un DEV (Genre un génie) qui s'est dit que le fait que newtoken n'existe pas en 9.0 est un bug pour la retro-compat de module builder
	// du coup il a fait une PR pour ça : maintenant il faut savoir qu'en fonction de la 9.0 de Doli c'est pile ou face pour avoir la fonction sur les clients...
	// Du génie je vous dis ! Grace à lui on peut gérer des problèmes que l'on n'aurait pas eus à la base...
	if(!function_exists('newToken')){

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

}
