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

$string['addbill'] = 'Add a bill';
$string['addbillitem'] = 'Add a bill item';
$string['abstract'] = 'Abstract';
$string['actualstate'] = 'Current status';
$string['allowingtax'] = 'Enable tax';
$string['allowtax'] = 'Allow tax';
$string['assignedto'] = 'Assigned to';
$string['backto'] = 'Back to:';
$string['bill'] = 'Bill:';
$string['proformabill'] = 'Online bill:';
$string['bill_ALLs'] = 'All';
$string['bill_CANCELLEDs'] = 'Bill cancelled';
$string['bill_COMPLETEs'] = 'Bill completed';
$string['bill_PLACEDs'] = 'Bill placed';
$string['bill_FAILEDs'] = 'Bill payment failed';
$string['bill_PARTIALs'] = 'Partial bill';
$string['bill_PAYBACKs'] = 'Bill payback';
$string['bill_PENDINGs'] = 'Bill pending';
$string['bill_RECOVERINGs'] = 'Bill in recovery';
$string['bill_SOLDOUTs'] = 'Bill paid';
$string['bill_PREPRODs'] = 'Bill in advanced prod';
$string['bill_REFUSEDs'] = 'Bill payment refused';
$string['bill_WORKINGs'] = 'Bill in progress';
$string['bill_assignation'] = 'Bill assigned';
$string['billdatefmt'] = 'Y-m-d';
$string['billstates'] = 'Bill statuses';
$string['billtaxes'] = 'Total tax:';
$string['billtitle'] = 'Title:';
$string['choosecustomer'] = 'Choose a customer';
$string['chooseuser'] = 'Choose a user';
$string['customer_account'] = 'Bill assigned to a customer account';
$string['deadline'] = 'Deadline';
$string['editbillitem'] = 'Edit bill item';
$string['expectedpaiement'] = 'Expected payment date';
$string['exportasxls'] = 'Export as XLS';
$string['fullviewon'] = 'Full view';
$string['fullviewoff'] = 'Short view';
$string['generateacode'] = 'Generate a code';
$string['goto'] = 'Go to:';
$string['lettering'] = 'Cross-reference coding';
$string['letteringupdated'] = 'Cross-reference code updated';
$string['nobillattachements'] = 'No document attached.';
$string['nobills'] = 'No bills';
$string['nocodegenerated'] = 'No transaction code has been generated (manual bills).';
$string['noletteringaspending'] = 'This is a pending order. <br/>Only a bill can be cross referenced.';
$string['paiedamount'] = 'Amount paid';
$string['paimentcode'] = 'Bill code:';
$string['paymodes'] = 'Method of payment';
$string['biquantity'] = 'Quantity:';
$string['searchtimerange'] = 'Estimated transaction period';
$string['seethecustomerdetail'] = 'See customer details';
$string['status'] = 'Status';
$string['timetodo'] = 'Completion date:';
$string['totalttc'] = 'Total, tax included:';
$string['totaltex'] = 'Total tax, not included:';
$string['totalti'] = 'Total:';
$string['transaction'] = 'Transaction';
$string['uniqueletteringfailure'] = '<a href="{$a}">Another bill</a> already has this cross reference.';
$string['unittex'] = 'Unit, tax not included:';
$string['updatelettering'] = 'Update';
$string['pickuser'] = 'Choose a customer or a user account:';
$string['worktype'] = 'Worktype';

$string['formula_creation_help'] = '
# Calculating tax using the tax formula

The user must enter the formula used to calculate the “tax included” price of an article

This formula must integrate variables of the “tax included” price ($TTC), the “tax not included” price ($HT), and the “tax ratio” ($TR)

Example: $TTC = $HT + ($HT*$TR/100)';

$string['description_help'] = '
# Help on writing text Writing text in Moodle works pretty much the way you would expect, but you also have the ability to include "smilies", "URL addresses", and a few HTML tags in your text.

## Smilies (emoticons)

<div class="indent">
  <p>
    To embed these small icons in your text, just type the associated code. These codes are like little pictures if you turn your head to the left when looking at them.
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
    Any "word" starting with <b>www.</b> or <b>http://</b> will automatically become a clickable link.
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
# Associating a customer ID with a bill

A bill must be associated with a customer account

A customer must have an account identified by an ID

Fill the client ID field with an existing customer account ID

If you want to view, create or manage a client account, please go to shop administration';

$string['shopform_help'] = '
# Account information

Ordering from this website requires an account

It is important to fill out **all** fields to create a customer account if you don\'t already have one

User must provide their first name, last name, city, country and email

The email provided must be properly formatted (example: customer@provider.com';

$string['taxhelp_help'] = '
# Associating a tax code with a product code

A product must be associated with a product code

Select a tax code from the list

If you want to create a tax, please go to the tax manager in administration';

$string['customer_account_help'] = '# Help on assigning a bill to a customer account

A bill must be assigned to a customer account.

Select either an existing customer or user account on the platform.

If a user account is selected, a customer account is automatically created and the bill will be assigned to this new customer account.';

$string['billstates_help'] = '
# Order and bill statuses

Orders and bills have a life cycle determined by a status and transition engine.

Possible statuses include:

In progress (IN PROGRESS)
:   When a back office operator manually creates an order, it takes the “In progress” status.

Placed (PLACED)
:   Orders transmitted by online customers are placed for payment but not confirmed.

Pending orders (PENDING)
:   Orders have been confirmed by the customer but payment used is offline or is delayed for an asynchronous response. Offline payments must be handled manually by a back office operator.

Paid bills (PAID)
:   Bill has been paid in full because an online immediate method of payment was used or because it was marked as such by a back office operator after physical payment was received.

Completed bills (COMPLETED)
:   Order has been paid and completed (by any method, whether automatically by enabling handlers or manually shipped and sent to customer in case of physical goods.

Cancelled orders (CANCELLED)
:   Back office operators can cancel orders that have not yet been officially entered into accounting.

Failed payment bills (FAILED)</dd>
:   These bills mark a failed online payment process.

Payback orders (PAYBACK)</dd>
:   Orders abusively billed or orders for which a customer can return their purchase, might be marked as payback orders to the benefit of the customer.</dl>';

$string['bill_assignation_help'] = '# Help on assigning a bill

A bill must be assigned to a user with bill processing rights

Select the user you want to assign bill processing to';

$string['allowtax_help'] = '# Help on enabling tax

You can disable tax for bills

If you disable tax, tax will not be included in the bill amount';

$sring['lettering_help'] = '
#Cross reference coding

Cross reference coding helps you match official accounting registers. According to some regulations, online bills cannot be
registered as official bills because of bill numbering and identification. Using the cross referencing field,
you will be able to match and document online bills with their matching IDNumber in your accounting
software.';
