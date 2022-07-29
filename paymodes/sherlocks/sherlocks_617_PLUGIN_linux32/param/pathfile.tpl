#########################################################################
#
#	Pathfile 
#
#	Liste des fichiers parametres utilises par le module de paiement
#
#########################################################################
#
#
#-------------------------------------------------------------------------
# Activation (YES) / D�sactivation (NO) du mode DEBUG
#-------------------------------------------------------------------------
#
DEBUG!<%%DEBUG%%>!
#
# ------------------------------------------------------------------------
# Chemin vers le r�pertoire des logos depuis le web alias  
# Exemple pour le r�pertoire www.merchant.com/sherlock/payment/logo/
# indiquer:
# ------------------------------------------------------------------------
#
D_LOGO!/local/shop/paymodes/sherlocks/sherlocks_617_PLUGIN_linux32/logo/!
#
# --------------------------------------------------------------------------
#  Fichiers param�tres li�s a l'api mercanet paiement	
# --------------------------------------------------------------------------
#
# fichier des  param�tres sherlocks
#
F_DEFAULT!<%%DIRROOT%%>/local/shop/paymodes/sherlocks/sherlocks_617_PLUGIN_linux32/param/parmcom.sherlocks!
#
# fichier param�tre commercant
#
F_PARAM!<%%DIRROOT%%>/local/shop/paymodes/sherlocks/sherlocks_617_PLUGIN_linux32/param/parmcom!
#
# certificat du commercant
#
F_CERTIFICATE!<%%DIRROOT%%>/local/shop/paymodes/sherlocks/sherlocks_617_PLUGIN_linux32/param/certif!
# 
# type du certificat 	
# 
F_CTYPE!php!
#
# --------------------------------------------------------------------------
# 	end of file
# --------------------------------------------------------------------------
