<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Lang for pdf document production
 *
 * @package   local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pdfgeneration'] = 'Réglages de la génération Pdf';

$string['pdfenabled'] = 'Activé';
$string['pdfenabled_desc'] = 'Active la génération de documents pdf';
$string['printconfig'] = 'Configuration générale Pdf';
$string['printconfig_desc'] = 'Un ensemble d\'attributs sérialisés';
$string['billtemplate'] = 'Modèle HTML alternatif de facture';
$string['billtemplate_desc'] = 'Un modèle de document HTML pour la facture. Voir le fichier
 /local/shop/templates/bill_default_pdf_template.mustache comme point de départ.';
$string['billpaidstamp'] = 'Tampon d\'acquittement';
$string['billpaidstamp_desc'] = 'Une image jpeg. La transparence n\'est PAS supportée dans tcpdf. Configuration d\'impression
 associée : <code>paidstampx</code>, <code>paidstampy</code>, <code>paidstampw</code> et <code>paidstamph</code>.';
