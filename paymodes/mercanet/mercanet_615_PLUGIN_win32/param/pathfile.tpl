#########################################################################
#
#	Pathfile g�n�r� � l'installation v2
#	Liste des fichiers parametres utilises par le module de paiement
#
#########################################################################
#-------------------------------------------------------------------------
# Activation (YES) / D�sactivation (NO) du mode DEBUG
#-------------------------------------------------------------------------
DEBUG!<%%DEBUG%%>!
#
# ------------------------------------------------------------------------
# Chemin vers le r�pertoire des logos depuis le web alias  
# Exemple pour le r�pertoire www.merchant.com/mercanet/payment/logo/
# indiquer:/mercanet/payment/logo/
# ------------------------------------------------------------------------
D_LOGO!/blocks/shop/paymodes/mercanet/mercanet_615_PLUGIN_win32/logo/!
#
# --------------------------------------------------------------------------
#  Fichiers parametres lies a l'api mercanet paiement
# --------------------------------------------------------------------------
# fichier des  parametres mercanet
F_DEFAULT!<%%DIRROOT%%>\blocks\shop\paymodes\mercanet\mercanet_615_PLUGIN_win32\param\parmcom.mercanet!
#
# fichier parametre commercant
F_PARAM!<%%DIRROOT%%>\blocks\shop\paymodes\mercanet\mercanet_615_PLUGIN_win32\param\parmcom.!
#
# certificat du commercant
F_CERTIFICATE!<%%DIRROOT%%>\blocks\shop\paymodes\mercanet\mercanet_615_PLUGIN_win32\param\certif!
F_CTYPE!php!
#
# --------------------------------------------------------------------------
# 	end of file
# --------------------------------------------------------------------------
