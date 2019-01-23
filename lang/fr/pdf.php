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

$string['pdfgeneration'] = 'Réglages de la génération Pdf';

$string['pdfenabled'] = 'Activé';
$string['pdfenabled_desc'] = 'Active la génération de documents pdf';
$string['printconfig'] = 'Configuration générale Pdf';
$string['printconfig_desc'] = 'Un ensemble d\'attributs sérialisés';
$string['billtemplate'] = 'Modèle HTML alternatif de facture';
$string['billtemplate_desc'] = 'Un modèle de document HTML pour la facture. Voir le fichier /local/shop/templates/bill_default_pdf_template.mustache comme point de départ.';
$string['billpaidstamp'] = 'Tampon d\'acquittement';
$string['billpaidstamp_desc'] = 'Une image jpeg. La transparence n\'est PAS supportée dans tcpdf. Configuration d\'impression associée : <code>paidstampx</code>, <code>paidstampy</code>, <code>paidstampw</code>, <code>paidstamph</code>.';
$string['docwatermarkimage'] = 'Filigranne de document';
$string['docwatermarkimage_desc'] = 'Une image jpeg. La transparence n\'est PAS supportée dans tcpdf. Des images trop définies peuvent altérer drastiquement la performance du générateur Pdf. Configuration d\'impression associée : <code>watermarkx</code>, <code>watermarky</code>, <code>watermarkw</code>, <code>watermarkh</code>.';
$string['doclogoimage'] = 'Logo';
$string['doclogoimage_desc'] = 'Une image jpeg. La transparence n\'est PAS supportée dans tcpdf. Configuration d\'impression associée : <code>logox</code>, <code>logoy</code>, <code>logow</code>, <code>logoh</code>.';
$string['docheaderimage'] = 'En-tête';
$string['docheaderimage_desc'] = 'Une image jpeg. La transparence n\'est PAS supportée dans tcpdf. Configuration d\'impression associée : code>headerx</code>, <code>headery</code>, <code>headerw</code>, <code>headerh</code>.';
$string['docfooterimage'] = 'Pied de page';
$string['docfooterimage_desc'] = 'Une image jpeg. La transparence n\'est PAS supportée dans tcpdf. Configuration d\'impression associée : <code>footerx</code>, <code>footery</code>, <code>footerw</code>, <code>footerh</code>.';
