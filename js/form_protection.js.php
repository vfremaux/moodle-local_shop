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
 * @package    local_shop
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require('../../../config.php');

header("Content-type: text/javascript");

?>
/* API Variables */

/* testnumeric(textfield)
 * tests a numeric whithout size constraint
 * textfield : the textfield that contains the numeric as a value
 *
 * testunsigned(textfield)
 * tests an unsigned numeric value whithout size constraint
 * textfield : the textfield that contains the numeric as a value
 *
 * testcp(textfield)
 * tests a zip code over 5 digits
 * textfield : the textfield that contains the zipcode as a value
 *
 * testdate(textfield,format)
 * tests a date expressed with the given format
 * textfield : the textfield that contains the date as a text value
 *
 * testidentifier(textfield, minchars)
 * tests identifier of minchar characters at least. Identifier MUST NOT have non alphanumeric chars
 * (excepting underscore)
 * textfield : the textfield that contains the identifier as a text value
 *
 * testmail(textfield)
 * tests an email format as box@domain.
 * textfield : the textfield that contains the mail address as a text value
 *
 * testpassword(textfield, minchars)
 * tests a password token
 * textfield : the textfield that contains the password as a text value
 *
 * setupper(textfield)
 * force uppercase
 * textfield : the textfield that contains the password as a text value
 *
 * capitalizefirst(textfield)
 * force uppercase for first letter
 * textfield : the textfield that contains the password as a text value
 *
 * capitalizewords(textfield)
 * force uppercase for first letter of each word
 * textfield : the textfield that contains the password as a text value
 *
 * checkedformlaunch(form)
 * vérifie le formulaire et le lance si pas d'erreur
 * form : nom du formulaire
 */

/* globals */

var DaysInMonths = new Array();
DaysInMonths[0] = 31;
DaysInMonths[1] = 29;
DaysInMonths[2] = 31;
DaysInMonths[3] = 30;
DaysInMonths[4] = 31;
DaysInMonths[5] = 30;
DaysInMonths[6] = 31;
DaysInMonths[7] = 31;
DaysInMonths[8] = 30;
DaysInMonths[9] = 31;
DaysInMonths[10] = 30;
DaysInMonths[11] = 31;

var dateErrors = new Array();
var mandatoryErrors = new Array();

/*
 * TESTNUMERIC
 * teste un champ numérique sans contrainte de taille
 *
 * textfield : le champ (objet) à tester
 */
function testnumeric(textfield) {
    var fieldvalue = "";
    var lect = 0;

    if (textfield.value == "") {
        return true;
    }

    while ( (lect < textfield.value.length) &&
        ( (textfield.value.charAt(lect) >= '0') &&
            (textfield.value.charAt(lect) <= '9') ) ||
               (textfield.value.charAt(lect) == ' ') || (textfield.value.charAt(lect) == '-')) {
        if (textfield.value.charAt(lect) != ' ') {
            fieldvalue += textfield.value.charAt(lect);
        }
        lect++;
    }
    if (lect != textfield.value.length) {
        alert ("<?php echo get_string('onlynumerics', 'local_shop') ?>");
        textfield.value = "";
        return false;
    }
    textfield.value = fieldvalue;
    return true;
}

/*
 * TESTUNSIGNED
 * teste un champ numérique non signé sans contrainte de taille
 *
 * textfield : le champ (objet) à tester
 */
function testunsigned(textfield) {
    var fieldvalue = '';
    var lect = 0;

    if (textfield.value == '') {
        return true;
    }

    while ( (lect < textfield.value.length) &&
         ( (textfield.value.charAt(lect) >= '0') &&
            (textfield.value.charAt(lect) <= '9') ) ||
               (textfield.value.charAt(lect) == ' ') ) {
       if (textfield.value.charAt(lect) != ' ') {
          fieldvalue += textfield.value.charAt(lect);
       }
       lect++;
    }
    if (lect != textfield.value.length) {
       alert ("<?php print_string('onlynumerics', 'local_shop') ?>");
       textfield.value = "";
       textfield.focus();
       return false;
    }
    textfield.value = fieldvalue;
    return true;
}

/*
 * TESTIDENTIFIER
 * teste les identifiants (identifiants)
 *
 * textfield : le champ (objet) à tester
 */
function testidentifier(textfield, minchars) {
    if (textfield.value == '') {
        return true;
    }

    if (textfield.value.length < minchars) {
       alert ("<?php print_string('mincharserror', 'local_shop') ?>\n " + minchars);
       textfield.value = '';
       textfield.focus();
       return false;
    }
    for (i = 0; i < textfield.value.length; i++) {
       if ( ((textfield.value.charAt(i) < 'a') ||
            (textfield.value.charAt(i) > 'z')) &&
                    ((textfield.value.charAt(i) < 'A') ||
                            !(textfield.value.charAt(i) > 'Z')) &&
                                    ((textfield.value.charAt(i) < '0') ||
                                            (textfield.value.charAt(i) > '9')) &&
                                                    textfield.value.charAt(i) != 'z') {
          alert ("Caractères autorisés :\n a à z, A à Z, 0 à 9 et _");
          return false;
       }
    }

    testcharacters(textfield);
    return true;
}

/*
 * TESTPASSWORD
 * teste les mots de passe en longueur et en identité
 *
 * textfield : le champ (objet) à tester
 */
function testpassword(textfield, minchars) {
    var fieldkey = textfield.name.substring(0, textfield.name.length - 1);
    var pass = fieldkey + "1";
    var echo = fieldkey + "2";

    if (textfield.name.substring(textfield.name.length - 1) == '2') {
        if (textfield.form.elements[pass].value != textfield.form.elements[echo].value) {
            alert ("<?php print_string('passwordconfirmerror', 'local_shop') ?>");
            textfield.form.elements[pass].value = '';
            textfield.form.elements[echo].value = '';
            textfield.form.elements[pass].focus();
            return false;
        }
    }
    if (textfield.value == '') {
       return true;
    }
    if (textfield.value.length < minchars) {
        alert ("<?php print_string('mincharserror', 'local_shop') ?>" + minchars);
        textfield.value = "";
        textfield.focus();
        return false;
    }
    testcharacters(textfield);
    return true;
}

/*
 * TESTDATE
 * teste une date en cohérence
 *
 * value : la valeur à remplir
 * size : la taille du champ de texte
 */
function testdate(aTextField, aFormat) {
    return true;
}

/*
 * SETUPPER
 * Force majuscule
 */
function setupper(textfield) {
    var fieldvalue = "";
    for (var i = 0; i < textfield.value.length; i++) {
        switch (textfield.value.charAt(i)) {
            case 'é' :
                fieldvalue += "e";
                break;
            case 'è' :
                fieldvalue += "e";
                break;
            case 'ê' :
                fieldvalue += "e";
                break;
            case 'à' :
                fieldvalue += "a";
                break;
            case 'ü' :
                fieldvalue += "u";
                break;
            case 'ù' :
                fieldvalue += "u";
                break;
            case 'î' :
                fieldvalue += "i";
                break;
            case 'ï' :
                fieldvalue += "i";
                break;
            case 'û' :
                fieldvalue += "u";
                break;
            case 'ô' :
                fieldvalue += "o";
                break;
            default:
                fieldvalue += textfield.value.charAt(i);
        }
    }
    textfield.value = fieldvalue.toUpperCase();
}

/*
 * CAPITALIZEFIRST
 * Force capitalisation sur le premier caractère
 *
 */
function capitalizefirst(textfield) {
    textfield.value = textfield.value[0].toUpperCase() + textfield.value.substring(1,textfield.value.length).toLowerCase();
}

/*
 * CAPITALIZEWORDS
 * Force capitalisation sur le premier caractère
 */
function capitalizewords(textfield) {
    var cap_state = 0;
    var fieldvalue = '';
    for (i = 0 ; i < textfield.value.length; i++) {
        switch (cap_state) {
            case 0 :
                // not word char
                if ((textfield.value.charAt(i) >= 'a' &&
                        textfield.value.charAt(i) <= 'z') ||
                                (textfield.value.charAt(i) >= 'A' &&
                                        textfield.value.charAt(i) <= 'Z') ||
                                                (textfield.value.charAt(i) >= '0' &&
                                                        textfield.value.charAt(i) <= '9')) {
                    cap_state = 1;
                    fieldvalue += new String(textfield.value.charAt(i)).toUpperCase();
                } else if (textfield.value.charAt(i) == 'é') {
                    fieldvalue += "E";
                } else if (textfield.value.charAt(i) == 'è') {
                    fieldvalue += "E";
                } else if (textfield.value.charAt(i) == 'ê') {
                    fieldvalue += "E";
                } else if (textfield.value.charAt(i) == 'à') {
                    fieldvalue += "A";
                } else {
                    fieldvalue += textfield.value.charAt(i);
                }
                break;
            case 1 :
                // first word char
                if ((textfield.value.charAt(i) >= 'a' &&
                        textfield.value.charAt(i) <= 'z') ||
                                (textfield.value.charAt(i) >= 'A' &&
                                        textfield.value.charAt(i) <= 'Z') ||
                                                (textfield.value.charAt(i) >= '0' &&
                                                        textfield.value.charAt(i) <= '9')) {
                    fieldvalue += textfield.value.charAt(i).toLowerCase();
                } else if ((textfield.value.charAt(i) == 'é') ||
                        (textfield.value.charAt(i) == 'è') ||
                                (textfield.value.charAt(i) == 'ê') ||
                                        (textfield.value.charAt(i) == 'à') ||
                                                (textfield.value.charAt(i) == 'ù') ||
                                                        (textfield.value.charAt(i) == 'ç') ||
                                                                (textfield.value.charAt(i) == 'ô') ||
                                                                        (textfield.value.charAt(i) == 'û') ||
                                                                                (textfield.value.charAt(i) == 'î')) {
                    fieldvalue += textfield.value.charAt(i);
                } else {
                    fieldvalue += textfield.value.charAt(i);
                    cap_state = 0;
                }
            }
        }
    }
    textfield.value = fieldvalue;
}

/*
 * CHECKDATE
 * Vérifie une date (avant soumission finale)
 */
function checkdate(aTextField, aFormat) {
    return;
}

/*
 * ZEROFILL
 * remplit une variable texte à valeur numérique avec des zéros non significatifs
 *
 * value : la valeur à remplir
 * size : la taille du champ de texte
 */
function zerofill(value, size) {
    var zerostring = "";
    var stringlength = value.toString(10).length + size;

    for (i=0; i < size; i++) {
        zerostring += "0";
    }
    zerostring += value;
    return zerostring.substring (stringlength - size, stringlength);
}

/*
 * ISMANDATORY
 * vérifie le caractère obligatoire de certains champs
 *
 * fieldname : nom du champ testé
 * form : nom du formulaire
 * La liste des champs obligatoires est fournie dans un champ caché
 * "mandatory"
 */
function isMandatory(fieldname, form) {
    var mandatory_list = " " + form.elements['MANDATORY'].value + " ";
    if (mandatory_list.indexOf(" " + fieldname + " ") == -1) {
        return "";
    }
    return fieldname;
}

/*
 * SUBMITFINALCHECK
 * vérifie le formulaire avant émission
 *
 * form : objet formulaire
 */
function submitfinalcheck(form, datecheck) {
    dateErrors = new Array();
    mandatoryErrors = new Array();
    for (i = 0, j = 0, k = 0; i < form.elements.length; i++) {
        prfx = form.elements[i].name.substring(form.elements[i].name.length - 2, form.elements[i].name.length);
        if (prfx == "_D" && datecheck != "NODATE") {
            var result = checkdate(form.elements[i]);
            if (result != "") {
                dateErrors[j] = result;
                j++;
            }
        }
        if (form.elements[i].value == "") {
            var result = isMandatory(form.elements[i].name, form)
            if (result != "") {
                mandatoryErrors[k] = result;
                k++;
            }
        }
    }
    if (dateErrors.length + mandatoryErrors.length == 0) {
        return true;
    }
    return false;
}

/*
 * CHECKEDFORMLAUNCH
 * vérifie le formulaire et le lance si pas d'erreur
 *
 * form : nom du formulaire
 */
function checkedformlaunch(formname) {
    if (submitfinalcheck(document.forms[formname], "") == true) {
        document.forms[formname].submit()
    } else {
        var message = "";
        if (dateErrors.length != 0) {
            message += "<?php print_string('dateformaterror', 'local_shop') ?>:\n";
            for (var i = 0; i < dateErrors.length; i++) {
                message += document.forms[formname].elements[dateErrors[i]+ "_datefault"].value + "\n";
            }
        }
        if (mandatoryErrors.length != 0) {
            message += "<?php print_string('mandatoryerror', 'local_shop') ?>\n";
            for (var i = 0; i < mandatoryErrors.length; i++) {
                message += document.forms[formname].elements[mandatoryErrors[i]+ "_mandatory"].value + "\n";
            }
        }
        alert(message);
    }
}

function checkform(formobject) {
    checkform(formobject, "");
}

function checkform(formobject, datecheck) {
    if (submitfinalcheck(formobject, datecheck) == true) {
        return true;
    } else {
        var message = "";
        if (dateErrors.length != 0) {
            message += "<?php print_string('dateformaterror', 'local_shop') ?>\n";
            for (var i = 0; i < dateErrors.length; i++) {
                message += formobject.elements[dateErrors[i]+ "_datefault"].value + "\n";
            }
        }
        if (mandatoryErrors.length != 0) {
            message += "<?php print_string('mandatoryerror', 'local_shop') ?>\n";
            for (var i = 0; i < mandatoryErrors.length; i++) {
                message += formobject.elements[mandatoryErrors[i]+ "_mandatory"].value + "\n";
            }
        }
        alert(message);
        return false;
    }
}

/*
 * EXTRACTCHARACTERS
 * vérifie si le champs testé ne contient pas de caractères interdits
 *
 * textfield : nom du champ testé
 */
function testcharacters(textfield) {
    var chaine = textfield.value;
    var message = "Présence de charactères interdits : ";
    var fault = "";
    var a = new RegExp("[éèêëàâôöùûïî\\?\\!:;,\\*\\%\\\'\\\"\\\\/]","gi");

    if (chaine.match(a) != null) {
        fault += chaine.match(a);
    }
    if (fault != "") {
        message += fault + "\n";
        alert(message);
        textfield.focus();
        return false;
    }
}