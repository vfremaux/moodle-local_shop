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
# Exemple pour le r�pertoire www.merchant.com/mercanet/payment/logo/
# indiquer:
# ------------------------------------------------------------------------
#
D_LOGO!/blocks/shop/paymodes/mercanet/mercanet_615_PLUGIN_linux64/logo/!
#
# --------------------------------------------------------------------------
#  Fichiers param�tres li�s a l'api mercanet paiement	
# --------------------------------------------------------------------------
#
# fichier des  param�tres mercanet
#
F_DEFAULT!<%%DIRROOT%%>/blocks/shop/paymodes/mercanet/mercanet_615_PLUGIN_linux64/param/parmcom.mercanet!
#
# fichier param�tre commercant
#
F_PARAM!<%%DIRROOT%%>/blocks/shop/paymodes/mercanet/mercanet_615_PLUGIN_linux64/param/parmcom!
#
# certificat du commercant
#
F_CERTIFICATE!<%%DIRROOT%%>/blocks/shop/paymodes/mercanet/mercanet_615_PLUGIN_linux64/param/certif!
# 
# type du certificat 	
# 
F_CTYPE!php!
#
# --------------------------------------------------------------------------
# 	end of file
# --------------------------------------------------------------------------
