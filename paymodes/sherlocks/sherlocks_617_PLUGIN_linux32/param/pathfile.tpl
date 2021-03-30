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
# Activation (YES) / Désactivation (NO) du mode DEBUG
#-------------------------------------------------------------------------
#
DEBUG!<%%DEBUG%%>!
#
# ------------------------------------------------------------------------
# Chemin vers le répertoire des logos depuis le web alias  
# Exemple pour le répertoire www.merchant.com/sherlock/payment/logo/
# indiquer:
# ------------------------------------------------------------------------
#
D_LOGO!/local/shop/paymodes/sherlocks/sherlocks_617_PLUGIN_linux32/logo/!
#
# --------------------------------------------------------------------------
#  Fichiers paramètres liés a l'api mercanet paiement	
# --------------------------------------------------------------------------
#
# fichier des  paramètres sherlocks
#
F_DEFAULT!<%%DIRROOT%%>/local/shop/paymodes/sherlocks/sherlocks_617_PLUGIN_linux32/param/parmcom.sherlocks!
#
# fichier paramètre commercant
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
