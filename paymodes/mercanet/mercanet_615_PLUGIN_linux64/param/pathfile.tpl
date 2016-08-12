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
# Exemple pour le répertoire www.merchant.com/mercanet/payment/logo/
# indiquer:
# ------------------------------------------------------------------------
#
D_LOGO!/blocks/shop/paymodes/mercanet/mercanet_615_PLUGIN_linux64/logo/!
#
# --------------------------------------------------------------------------
#  Fichiers paramètres liés a l'api mercanet paiement	
# --------------------------------------------------------------------------
#
# fichier des  paramètres mercanet
#
F_DEFAULT!<%%DIRROOT%%>/blocks/shop/paymodes/mercanet/mercanet_615_PLUGIN_linux64/param/parmcom.mercanet!
#
# fichier paramètre commercant
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
