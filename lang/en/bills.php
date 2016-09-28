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

$string['addbill'] = 'Add bill';
$string['abstract'] = 'Abstract';
$string['actualstate'] = 'Current state';
$string['allowingtax'] = 'allowing tax';
$string['allowtax'] = 'Allow tax';
$string['assignedto'] = 'Assigned to';
$string['backto'] = 'Back to:';
$string['bill'] = 'Bill:';
$string['proformabill'] = 'Online Bill:';
$string['bill_ALLs'] = 'All';
$string['bill_CANCELLEDs'] = 'Bill cancelled';
$string['bill_COMPLETEs'] = 'Bill finished';
$string['bill_PLACEDs'] = 'Bill placed';
$string['bill_FAILEDs'] = 'Bill payment failed';
$string['bill_PARTIALs'] = 'Partial bill';
$string['bill_PAYBACKs'] = 'Bill payback';
$string['bill_PENDINGs'] = 'Pending bill';
$string['bill_RECOVERINGs'] = 'Bill recovering';
$string['bill_SOLDOUTs'] = 'Bill soldout';
$string['bill_PREPRODs'] = 'Bill in advanced prod';
$string['bill_WORKINGs'] = 'Bill working';
$string['bill_assignation'] = 'bill assignation';
$string['billstates'] = 'Bill states';
$string['billtaxes'] = 'Total taxes:';
$string['billtitle'] = 'Title';
$string['choosecustomer'] = 'Choose a customer';
$string['chooseuser'] = 'Choose an user';
$string['customer_account'] = 'the bill assignation to a customer account';
$string['deadline'] = 'Deadline';
$string['expectedpaiement'] = 'Expected date for payment';
$string['exportasxls'] = 'Export as XLS';
$string['generateacode'] = 'Generate a code';
$string['goto'] = 'Go to:';
$string['lettering'] = 'Lettering';
$string['letteringupdated'] = 'Lettering updated';
$string['nobillattachements'] = 'No document attached.';
$string['nobills'] = 'No bills';
$string['nocodegenerated'] = 'No transaction code has been generated (manual bills).';
$string['noletteringaspending'] = 'This is a pending order. <br/>Only a bill can be lettered.';
$string['paiedamount'] = 'Paied amount';
$string['paimentcode'] = 'Bill code:';
$string['paymodes'] = 'Paymode';
$string['searchtimerange'] = 'Probable period for the transaction';
$string['seethecustomerdetail'] = 'See customer details';
$string['status'] = 'Status';
$string['timetodo'] = 'Date to complete:';
$string['totalTTC'] = 'Total tax included:';
$string['totaltex'] = 'Total wt taxes:';
$string['totalti'] = 'Total:';
$string['transaction'] = 'Transaction';
$string['uniqueletteringfailure'] = '<a href="{$a}">Another bill</a> uses already this lettering code. ';
$string['unittex'] = 'Unit wt taxes:';
$string['updatelettering'] = 'Update';
$string['pickuser'] = 'Choose a customer account or an user account';
$string['worktype'] = 'Worktype';

$string['formula_creation_help'] = '
# Tax formula edition

User have to enter the tax formula, which calculate taxed price of an article

This formula must integrate variables of the taxed price ($TTC), the non-taxed price ($HT), the ratio of the tax ($TR)

Exemple : $TTC = $HT + ($HT*$TR/100)';

$string['description_help'] = '
# Help on writing text Writing text in Moodle works pretty much the way you would expect, but you also have the ability to include "smilies", "URL addresses" and some HTML tags in your text.

## Smilies (emoticons)

<div class="indent">
  <p>
    To embed these small icons in your text, just type the associated code. These codes themselves are like little pictures if you turn your head to the left when looking at them.
  </p>

  <table border="1">
    <tr valign="top">
      <td>
        <table border="0" cellpadding="10">
          <tr>
            <td>
              <img alt="" src="pix/s/smiley.gif" class="icon" />
            </td>

            <td>
              smile
            </td>

            <td>
              <code>:-)</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/biggrin.gif" class="icon" />
            </td>

            <td>
              big grin
            </td>

            <td>
              <code>:-D</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/wink.gif" class="icon" />
            </td>

            <td>
              wink
            </td>

            <td>
              <code>;-)</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/mixed.gif" class="icon" />
            </td>

            <td>
              mixed
            </td>

            <td>
              <code>:-/</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/thoughtful.gif" class="icon" />
            </td>

            <td>
              thoughtful
            </td>

            <td>
              <code>V-.</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/tongueout.gif" class="icon" />
            </td>

            <td>
              tongue out
            </td>

            <td>
              <code>:-P</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/cool.gif" class="icon" />
            </td>

            <td>
              cool
            </td>

            <td>
              <code>B-)</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/approve.gif" class="icon" />
            </td>

            <td>
              approve
            </td>

            <td>
              <code>^-)</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/wideeyes.gif" class="icon" />
            </td>

            <td>
              wide eyes
            </td>

            <td>
              <code>8-)</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/surprise.gif" class="icon" />
            </td>

            <td>
              surprise
            </td>

            <td>
              <code>8-o</code>
            </td>
          </tr>
        </table>
      </td>

      <td>
        <table border="0" cellpadding="10">
          <tr>
            <td>
              <img alt="" src="pix/s/sad.gif" class="icon" />
            </td>

            <td>
              sad
            </td>

            <td>
              <code>:-(</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/shy.gif" class="icon" />
            </td>

            <td>
              shy
            </td>

            <td>
              <code>8-.</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/blush.gif" class="icon" />
            </td>

            <td>
              blush
            </td>

            <td>
              <code>:-I</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/kiss.gif" class="icon" />
            </td>

            <td>
              kisses
            </td>

            <td>
              <code>:-X</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/clown.gif" class="icon" />
            </td>

            <td>
              clown
            </td>

            <td>
              <code>:o)</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/blackeye.gif" class="icon" />
            </td>

            <td>
              black eye
            </td>

            <td>
              <code>P-|</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/angry.gif" class="icon" />
            </td>

            <td>
              angry
            </td>

            <td>
              <code>8-[</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/dead.gif" class="icon" />
            </td>

            <td>
              dead
            </td>

            <td>
              <code>xx-P</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/sleepy.gif" class="icon" />
            </td>

            <td>
              sleepy
            </td>

            <td>
              <code>|-.</code>
            </td>
          </tr>

          <tr>
            <td>
              <img alt="" src="pix/s/evil.gif" class="icon" />
            </td>

            <td>
              evil
            </td>

            <td>
              <code>}-]</code>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>

## URLs

<div class="indent">
  <p>
    Any "word" starting with <b>www.</b> or <b>http://</b> will automatically be turned into a clickable link.
  </p>

  <p>
    For example: <a href="http://www.yahoo.com">www.yahoo.com</a> or <a href="http://curtin.edu">http://curtin.edu</a>
  </p>
</div>

## HTML tags

<div class="indent">
  <p>
    You can use a limited subset of HTML tags to add emphasis to your texts.
  </p>

  <table border="0" cellpadding="5" cellspacing="5">
    <tr>
      <th scope="col">
        HTML tags
      </th>

      <th scope="col">
        Produces
      </th>
    </tr>

    <tr>
      <td>
        <b> bold </b>
      </td>

      <td>
        <b>bold text</b>
      </td>
    </tr>

    <tr>
      <td>
        <i> italic </i>
      </td>

      <td>
        <i>italic text</i>
      </td>
    </tr>

    <tr>
      <td>
        <u> underline </u>
      </td>

      <td>
        <u>underlined text</u>
      </td>
    </tr>

    <tr>
      <td>
        <font color="green"> example </font>
      </td>

      <td>
        <font color="green">example</font> </tr> <tr>
          <td valign="top">
            <ul> <li>one</li> <li>two</li> </ul>
          </td>

          <td valign="top">
            <ul>
              <li>
                one<li>
                  two</ul> </tr> <tr>
                    <td>
                      <hr />
                    </td>

                    <td>
                      <hr />
                    </td>
                  </tr></table> </div>';

$string['help_userid_help'] = '
# Associate client id with a bill

A bill must be associated with a client account

Customer must have an account, identified by an id

Fill the client Id field with an existing customer\'s account Id

If you want to consult, create or manage a client\'s account, please go on the administration of the shop';

$string['shopform_help'] = '
# Account informations

Order on this website require an account

It is important to fill **all** the fields to create a customer account if you don\'t have one

User must give his firstname, lastname, city, country and his mail

The mail given must be in a good format (exemple : customer@provider.com';

$string['taxhelp_help'] = '
# Associate a tax code to a product code

A product must be associated to a product code

Select a tax code among tax code proposed

If you want to create a tax, please go on the tax manager in the administration of this block';

$string['customer_account_help'] = '# Help on customer account assignation for bills

Bill must be assigned to a customer account.

Select between an existing customer account, or an user account on the plateform.

If an user account is selected, a customer account is automaticly created and the bill will be assigned to this new customer account.';

$string['billstates_help'] = '
# States for orders and bills

Orders and bills have a lifecycle driven by a state and transition engine.

following states are used:

Working (WORKING)
:   When a backoffice operator creates manually an order, it takes the Working state.

Placed (PLACED)
:   Orders sent by online customers so placed for payment but not confirmed.

Pending orders (PENDING)
:   Orders have been confirmed by customer, but payement used is offline or is delayed to an asynchronous response. Offline payement reception must be handled manually by a back-office operator.

Soldout bills (SOLDOUT)
:   Bill have been sold out integrally because of using an online immediate payment method, or because having been so marked by a back-office operator after physical payement reception.

Completed bills (COMPLETE)
:   Order has been payed and realized (in any way, automatically by playing handlers, or manually shipped and sent to customer in case of physical goods.

Cancelled orders (CANCELLED)
:   Back-office operators an cancel orders that have not been entered into real accountance path.

Failed payement bills (FAILED)</dd>
:   This bills mark online payement failed process.

Payback orders (PAYBACK)</dd>
:   Orders abusively invoiced or in case of possibility of the customer to revert his purchase might be marked as payback orders at benefit of the customer.</dl>';

$string['bill_assignation_help'] = '# Help on the bill assignation

A bill traitment must be assignate to an user with bill traitment right

Select the user you want to assign bill traitment to';

$string['allowtax_help'] = '# Help on allowing tax

You can desactivate tax for bills

If you desactivate tax, tax will not be included in the amount of the bill';

$sring['lettering_help'] = '
#Lettering

Lettering help you to match your official accountance registers. On line bills in some regulations cannot be
registered as official invoices, because of invoice numbering and identification. Using the letterig field,
you will be able to match and document the online bills with their matching IDNumber in your accountance
software.';