<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,00
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot.'/local/shop/lib.php');

// Capabilities.

$string['shop:salesadmin'] = 'Is sales admin';
$string['shop:beassigned'] = 'Be assigned';
$string['shop:accessallowners'] = 'Can view all subowners data and products';
$string['shop:discountagreed'] = 'Will have some standard discount on all products';
$string['shop:export'] = 'Can export shop internal definition';
$string['shop:seconddiscountagreed'] = 'Will have a better discount rate on all products';
$string['shop:thirddiscountagreed'] = 'Will have the best discount rate on all products';
$string['shop:paycheckoverride'] = 'Will have products realized even when paying with non instant payments';
$string['shop:usenoninstantpayments'] = 'Can use non instant payment methods';

// Transaction states.

$string['ABANDONNED'] = 'Exhausted';
$string['AVAILABLE'] = 'Available';
$string['AVAILABLEINTERNAL'] = 'Avail. (internal)';
$string['ASCOMPLEMENT'] = 'Avail. (existing product complement)';
$string['CANCELLED'] = 'Cancelled';
$string['COMPLETE'] = 'Complete (processed)';
$string['DELAYED'] = 'Confirmed paiment in progress';
$string['PLACED'] = 'Unconfirmed';
$string['PARTIAL'] = 'Partially paied';
$string['PAYBACK'] = 'Payback';
$string['PENDING'] = 'Pending';
$string['PREPROD'] = 'Produced by anticipation';
$string['PREVIEW'] = 'Preview';
$string['PROVIDING'] = 'Providing';
$string['RECOVERING'] = 'Recovering';
$string['REFUSED'] = 'Refused';
$string['SOLDOUT'] = 'Payed out';
$string['SUSPENDED'] = 'Suspended';
$string['WORKING'] = 'On work (internal)';

$string['abstract'] = 'Abstract';
$string['addcustomeraccount'] = 'Add customer account';
$string['admin'] = 'Administration';
$string['after'] = 'after';
$string['allbills'] = 'All bills';
$string['allcustomers'] = 'All customers';
$string['allowtax'] = 'Apply taxes';
$string['allowtax_help'] = 'If enabled, will apply all local taxes on product calculations';
$string['allproductinstances'] = 'All product instances';
$string['allproducts'] = 'All products';
$string['allshops'] = 'All shop instances';
$string['allshops_desc'] = 'All shops';
$string['alltaxes'] = 'Taxes (cumulated)';
$string['apparence'] = 'Apparence';
$string['amonth'] = 'one month';
$string['amount'] = 'Amount';
$string['apparence'] = 'Apparence';
$string['attach'] = 'Attach a file';
$string['attachements'] = 'Attachments';
$string['availability'] = 'Availability';
$string['availableproducts'] = 'Available products';
$string['backoffice'] = 'Back Office';
$string['backtocatalog'] = 'Back to catalog';
$string['backtoshop'] = 'Back to shop';
$string['backtoshopadmin'] = 'Back to shop administration';
$string['bankaccount'] = 'Bank Account';
$string['bankaccountkey'] = 'Bank Account Key';
$string['bankcode'] = 'Bank code';
$string['banking'] = 'Banking';
$string['bankinginfo'] = 'Reseller banking information';
$string['bankoffice'] = 'Bank Office';
$string['banktransfers'] = 'Bank tranfer';
$string['bic'] = 'BIC';
$string['billdate'] = 'Emission date';
$string['billid'] = 'Bill num';
$string['billpaidstamp'] = 'Bill paied stamp';
$string['billpaidstamp_desc'] = 'Une image PNG ou JPG';
$string['bills'] = 'Bills';
$string['billsdeleted'] = 'Bills/ordering deleted';
$string['billsearch'] = 'Bills : Search';
$string['billspending'] = 'Bills : Pending';
$string['billtotal'] = 'Bill Total';
$string['blancktransactioncode'] = 'Transaction code has never been generated (handy billing)';
$string['bundle'] = 'Bundle';
$string['carefullchoice'] = 'Check the default paymode you choose is enabled !!.';
$string['catalog'] = 'Catalog ';
$string['catalogadmin'] = 'Catalog management';
$string['catalogsdeleted'] = 'Catalogs data deleted';
$string['catalogue'] = 'Product line';
$string['cataloguemanagement'] = 'Product line management';
$string['catalogues'] = 'Product lines';
$string['catdescription'] = 'Description';
$string['category'] = 'Categories';
$string['categorydescription'] = 'Description';
$string['categoryname'] = 'Name';
$string['categoryowner'] = 'Category Owner';
$string['catname'] = 'Name';
$string['catnum'] = 'Number';
$string['check'] = 'Check';
$string['checkpasswordemission'] = 'Check password emission';
$string['chooseall'] = 'All';
$string['clear'] = 'clear file';
$string['code'] = 'Code';
$string['configbankaccount'] = 'Bank account of the merchant';
$string['configbankaccountkey'] = 'Account key of the merchant';
$string['configbankcode'] = 'Bank code of the merchant';
$string['configbanking'] = 'Bank company name';
$string['configbankoffice'] = 'Agency code';
$string['configbic'] = 'BIC identification code of the merchant\'s bank';
$string['configcatalog'] = 'Catalog to bind';
$string['configcurrency'] = 'Currency';
$string['configcustomerorganisationrequired'] = 'Organisation required (customer)';
$string['configdefaultcurrency'] = 'Currency suffix';
$string['configdefaultcustomersupportcourse'] = 'Default course for customer support';
$string['configdiscountrate'] = 'A discount rate applied on the overall amount. May be applied inconditionally if customer has the local/shop:discountagreed capability on';
$string['configdiscountrate2'] = 'A discount rate applied when customer has the local/shop:seconddiscountagreed capability on';
$string['configdiscountrate3'] = 'A discount rate applied when customer has the local/shop:thirddiscountagreed capability on';
$string['configdiscounttheshold'] = 'A threshold that triggers the effectivity of the discount';
$string['extradataonproductinstances'] = 'Extra data on product instances list';
$string['configextradataonproductinstances'] = 'One or more (coma separated) extradata field to display under the product refrence in the product instances list. Note that this is strongly bound to the technical implementation.';
$string['configelementimageheight'] = 'Product subelement thumb height (px)';
$string['configelementimagermargin'] = 'Product subelement thumb right margin (px)';
$string['configelementimagewidth'] = 'Product subelement thumb width (px)';
$string['configendusermobilephonerequired'] = 'Mobile phone required (end user)';
$string['configenduserorganisationrequired'] = 'Organisation required (end user)';
$string['configeula'] = 'EULAs';
$string['confightaccesscred'] = 'If the moodle is operated behind an http authenication, the auth credentials to give for third party return urls';
$string['configiban'] = 'The IBAN number of the merchant';
$string['configmaxitemsperpage'] = 'Max items per page';
$string['configprinttabbedcategories'] = 'Print categories as tabs';
$string['configproductimageheight'] = 'Product thumb height (px)';
$string['configproductimagermargin'] = 'Product thumb right margin (px)';
$string['configproductimagewidth'] = 'Product thumb width (px)';
$string['configsellerID'] = 'The official company registrar ID of the seller';
$string['configselleraddress'] = 'The address (postal) of the merchant';
$string['configsellerbillingaddress'] = 'The accountance location of the merchant';
$string['configsellerbillingcity'] = 'City of accountance service';
$string['configsellerbillingcountry'] = 'Country of accountant service';
$string['configsellerbillingzip'] = 'Zip code of the accountant service';
$string['configsellercity'] = 'City of the merchant';
$string['configsellercountry'] = 'Country of the merchant';
$string['configsellerlogo'] = 'the logo that will be printed on invoices and orders';
$string['configsellermail'] = 'Mail address of the merchant';
$string['configsellermailsupport'] = 'Mail address of the sales customer support team';
$string['configsellername'] = 'The official name of the vendor';
$string['configsellerphonesupport'] = 'Phone number of the sales customer support team';
$string['configsellerzip'] = 'Zip code for the seller identity';
$string['configserviceproxykey'] = 'A service proxy may be relaying some service requests from your ditributed products into the purchase management servcice.';
$string['configshopcaption'] = 'Shop caption';
$string['configshopdescription'] = 'Shop description';
$string['configtestmode'] = 'Enables the mode test for the payments';
$string['configtestoverride'] = 'Overrides test lock on purchase to test simulated external users';
$string['configtvaeurope'] = 'European VAT number';
$string['configusedelegation'] = 'If enabled, some users may get ownership of products and sales in the shop.';
$string['configuserenewableproducts'] = 'Uses renewable products';
$string['configuseshipping'] = 'Enables the shipping costs engine';
$string['configuseslavecatalogs'] = 'Uses master/slave catalogs';
$string['confirmoperation'] = 'Confirm operation';
$string['controls'] = 'Controls';
$string['countrycodelist'] = 'Country codes list';
$string['countryrestrictions'] = 'Country restrinctions';
$string['courseowner'] = 'Course Owner';
$string['courseowner_desc'] = 'The Course Owner is an editing teacher that owns the course space';
$string['createlocalversion'] = 'Create a local version';
$string['currentowner'] = 'Current owner';
$string['customeraccount'] = 'Customer account';
$string['customeraccounts'] = 'Customer acocunts';
$string['customername'] = 'Customer name';
$string['customerrole_desc'] = 'People who have purchased in the shop system';
$string['customerrolename'] = 'Customer';
$string['customers'] = 'Customers';
$string['customersdeleted'] = 'Customer base deleted';
$string['dedicated'] = 'Specific';
$string['defaultbilltitle'] = '{$a} Online Purchase';
$string['defaultcurrency'] = 'Default Currency';
$string['defaultprivatemessagepostpay'] = 'Purchase of {$a->quantity} {$a->abstract}';
$string['defaultpublicmessagepostpay'] = 'You have purchased {$a->quantity} {$a->abstract}';
$string['defaultsalesadminmessagepostpay'] = '{$a->quantity} {$a->abstract} purchased.';
$string['deletealllinkedproducts'] = 'Delete all linked products';
$string['deletebillitems'] = 'Delete bill items';
$string['deletebills'] = 'Delete bills';
$string['deletelocalversion'] = 'Delete local version';
$string['deleteproduct'] = 'Delete product';
$string['description'] = 'Description:';
$string['disable'] = 'Disable';
$string['disabled'] = 'Disabled';
$string['discountrate'] = 'Discount Rate';
$string['discountrate2'] = 'Discount Rate 2';
$string['discountrate3'] = 'Discount Rate 3';
$string['discounts'] = 'Discount settings';
$string['discountthreshold'] = 'Discount Threshold';
$string['dispo'] = 'Avail.';
$string['dosearch'] = 'Search';
$string['downloadpdfbill'] = 'Download Pdf Invoice';
$string['edit_categories'] = 'Edit categories';
$string['editbundle'] = 'Bundle editing';
$string['editcatalog'] = 'Update catalog definition';
$string['editcategories'] = 'Edit categories';
$string['editcategory'] = 'Edit category';
$string['editproduct'] = 'Product editing';
$string['editset'] = 'Set editing';
$string['editshop'] = 'Edit a shop instance';
$string['editshopsettings'] = 'Edit shop settings';
$string['elementimageheight'] = 'Element thumb height';
$string['elementimagermargin'] = 'Element thumb right margin';
$string['elementimagewidth'] = 'Element thumb width';
$string['enablehandler'] = 'Enable product handler';
$string['enable'] = 'Enable';
$string['enablepaymodes'] = 'Enable paymodes';
$string['error'] = 'Error: ';
$string['erroraddbill'] = 'Could not add bill record';
$string['erroraddbillitem'] = 'could not add bill item';
$string['erroraddbundle'] = 'Could not add bundle';
$string['erroraddcategory'] = 'Could not add a category';
$string['erroraddcustomer'] = 'Could not add customer';
$string['erroraddnewcustomer'] = 'Cannot record a new customer account';
$string['erroraddproduct'] = 'Could not add product';
$string['erroraddset'] = 'Could not add a set';
$string['errorbadcatalogid'] = 'Bad catalog ID';
$string['errorbadformatrenderer'] = 'No format renderer for export';
$string['errorbadhandler'] = 'The handler class ({$a}) file does not exist. This is a coding error that should be reported to Moodle Shop developers.';
$string['errorbadview'] = 'This view does not exist';
$string['erroremailexists'] = 'Email address already associated to a customer account';
$string['erroremptycountry'] = 'Empty country';
$string['erroremptyexport'] = 'No data source for export';
$string['errorexcelcreation'] = 'Excel failure. Workbook not created';
$string['errorinvalidblockID'] = 'Invalid block';
$string['errormisconfigured'] = 'Block is not configured';
$string['errormissingactiondata'] = 'Missing handler action data for {$a} standard handler';
$string['errormissingview'] = 'Invalid view';
$string['errornoguests'] = 'Guests are not allowed here';
$string['errornoselleridentity'] = 'Shop parameters have not been defined.';
$string['errornotallowed'] = 'You are not allowed to be here.';
$string['errornotownedbill'] = 'This bill is not owned by you.';
$string['errorprogramming'] = 'Programming Error. Never let do';
$string['errorregisterorder'] = 'Error registering a bill';
$string['errorrequirementfieldtype'] = 'Unrecognized field type {$a} in product definition';
$string['errorunimplementedhandlermethod'] = 'This postprod method ({$a}) is not implemented in this handler. This is a coding error that should be reportedto the Moodle Shop developers.';
$string['errorupdatebill'] = 'Could not update bill record';
$string['errorupdatebillitem'] = 'could not update bill item';
$string['errorupdatebundle'] = 'Could not update bundle';
$string['errorupdatecategory'] = 'Could not update category';
$string['errorupdateproduct'] = 'Could not update product';
$string['errorupdateset'] = 'Could not update set';
$string['eula'] = 'Text of eulas';
$string['eulaagree'] = 'Confirm agreement to EULA';
$string['eulaheading'] = 'End User Licence Agreement';
$string['experimental'] = 'Experimental features';
$string['exportid'] = 'ID';
$string['exportinstitution'] = 'Institution';
$string['exportdepartment'] = 'Department';
$string['exportpartner'] = 'Partner';
$string['exportdiscountcodes'] = 'Discounts';
$string['exportaddress'] = 'Address';
$string['exportamount'] = 'TI';
$string['exportcity'] = 'City';
$string['exportcountry'] = 'Country';
$string['exportemail'] = 'Email';
$string['exportemissiondate'] = 'created';
$string['exportfirstname'] = 'FirstN';
$string['exportidnumber'] = 'IDNum';
$string['exportitemnames'] = 'Product Names';
$string['exportitems'] = 'Items';
$string['exportlastactiondate'] = 'lastmove';
$string['exportlastname'] = 'LastN';
$string['exportonlinetransactionid'] = 'OnlineTxID';
$string['exportshortnames'] = 'Shortnames';
$string['exportstatus'] = 'ST';
$string['exporttaxes'] = 'TX';
$string['exporttitle'] = 'title';
$string['exporttransactionid'] = 'TxID';
$string['exportuntaxedamount'] = 'Neat';
$string['exportusername'] = 'Login';
$string['exportworktype'] = 'WT';
$string['exportzip'] = 'ZIP';
$string['flowControlNetEnd'] = '<span style="color:#A0A0A0;font-weight:bolder">(FIN DE TRANSACTION)</span>';
$string['flowControlNetStart'] = '<span style="color:#A0A0A0;font-weight:bolder">(DEBUT DE TRANSACTION)</span>';
$string['formula_creation'] = 'tax edition';
$string['from'] = 'from (date)';
$string['fromdate'] = 'From';
$string['fulldatefmt'] = '%Y-%m-%d %H:%M';
$string['generalsettings'] = 'Access general settings form in Moodle administration';
$string['generateacode'] = 'Generate a code';
$string['generic'] = 'Generic:';
$string['genericerror'] = 'Internal Error: {$a}';
$string['globalsettings'] = 'Global settings';
$string['gotest'] = 'Start test';
$string['gotobackoffice'] = 'Go to products backoffice';
$string['gotofrontoffice'] = 'Go to shop frontoffice';
$string['help_informations'] = 'the data fill';
$string['helpdescription'] = 'the description';
$string['helptax'] = 'the association of product code and tax code';
$string['hour'] = '(hour)';
$string['htaccesscred'] = 'HTTP auth for returns (test)';
$string['iban'] = 'IBAN';
$string['identifiedby'] = 'identified by';
$string['image'] = 'Image:';
$string['instancesettings'] = 'Instance settings';
$string['isdefault'] = 'Default';
$string['items'] = 'Items';
$string['knownaccount'] = 'Known account';
$string['label'] = 'Label:';
$string['leaflet'] = 'Leaflet:';
$string['leafletlink'] = 'Download the leaflet';
$string['leafleturl'] = 'Leaflet URL:';
$string['link'] = 'Link ';
$string['login'] = 'You have a customer account ';
$string['maillog'] = 'End of mail log file:';
$string['managediscounts'] = 'Manage discounts';
$string['managediscounts_desc'] = 'Manage discounts instances against several discount policies';
$string['manageshipping'] = 'Manage shipping';
$string['manageshipping_desc'] = 'Manages shipping definitions';
$string['managetaxes'] = 'Manage taxes';
$string['managetaxes_desc'] = 'Manages tax definitions';
$string['manybillsasresult'] = 'Several bills are matching your actual criterias. Choose in&nbsp;';
$string['manyunitsasresult'] = 'Several product instances are matching your actual criterias. Choose in&nbsp;';
$string['master'] = 'Master catalog';
$string['maxdeliveryquant'] = 'Max quantity per transaction:';
$string['maxitemsperpage'] = 'Max items';
$string['message'] = 'Message:';
$string['miscellaneous'] = 'Miscellaneous';
$string['missingcode'] = 'Code must be given';
$string['mytotal'] = 'See your cart total';
$string['name'] = 'Name:';
$string['namecopymark'] = ' - Copy';
$string['newbill'] = 'New bill';
$string['newbillitem'] = 'New bill item';
$string['newcatalog'] = 'New catalog';
$string['newshop'] = 'New shop instance';
$string['newshopinstance'] = 'New shop';
$string['nocatalogs'] = 'No catalog available';
$string['nocats'] = 'No categories';
$string['nocolumns'] = 'No colums';
$string['none'] = 'None';
$string['nonmutable'] = 'Non mutable product';
$string['nopaymodesavailable'] = 'No paymodes available. This may be because of a misconfiguration of this shop, or particular test conditions.';
$string['nosamecurrency'] = 'All bills have not the same curency. Sum is not consistant';
$string['noshops'] = 'No shops defined';
$string['notes'] = 'Notes';
$string['notowner'] = 'You are not owning this item';
$string['notrace'] = 'No trace for this transaction';
$string['num'] = 'N°';
$string['objectexception'] = 'Object exception : {$a}';
$string['oneday'] = 'a day';
$string['onehour'] = 'one hour';
$string['onlyforloggedin'] = 'Only for logged in users:';
$string['or'] = 'or';
$string['order'] = 'Order';
$string['orderType_OTHER'] = 'Other products or service';
$string['orderType_PACK'] = 'Standard';
$string['orderType_PROD'] = 'Product';
$string['orderType_SERVICE'] = 'Service';
$string['orders'] = 'Orders';
$string['outofcategory'] = 'Out of category (root)';
$string['outofset'] = 'Out of set';
$string['partnerkey'] = 'Partner key';
$string['pastetransactionid'] = 'Paste a transaction ID ';
$string['paymentmethods'] = 'Pay modes';
$string['picktransactionid'] = 'Pick a transaction ID ';
$string['pluginname'] = 'e-Shop';
$string['pluginname'] = 'shop';
$string['postproduction'] = 'Product action';
$string['potentialhandlererror'] = 'Sales admin only warning : the product {$a} seems having handler but mismatched course association.';
$string['plugindist'] = 'Plugin distribution';
$string['price'] = 'Price';
$string['price2'] = 'Price 2';
$string['price3'] = 'Price 3';
$string['printbill'] = 'See printable versions';
$string['printbilllink'] = 'Print this invoice now !';
$string['printlink'] = 'Print';
$string['produclabel'] = 'Product label';
$string['product'] = 'Product:';
$string['productcode'] = 'Product code:';
$string['productcount'] = 'Products';
$string['productid'] = 'Product ID';
$string['productimageheight'] = 'Thumb height';
$string['productimagermargin'] = 'Thumb right margin';
$string['productimagewidth'] = 'Thumb width';
$string['productioncomplete'] = 'Your order has been processed.';
$string['productionresults'] = 'Production results';
$string['productline'] = 'Poduct line';
$string['productname'] = 'Product name';
$string['productoperation'] = 'Operation on your product';
$string['productpostprocess'] = 'Post production product actions';
$string['products'] = 'Products:';
$string['profroma'] = 'Invoice';
$string['providedbymoodleshop'] = 'Group built by Moodle Shop';
$string['provisionalnumber'] = 'Provisional numbering';
$string['quant'] = 'Quant';
$string['quantaddressesusers'] = 'Quantity addresses user seats';
$string['quantity'] = 'Quantity';
$string['rate'] = 'Rate';
$string['recalculate'] = 'Recalculate';
$string['ref'] = 'Ref';
$string['removesecurity'] = ' confirm catalogs deletion: ';
$string['required'] = 'Required field';
$string['requiredformaterror'] = 'It seems the required data description format is not a parsable JSON string';
$string['requiredparams'] = 'Required field definition';
$string['reset'] = 'Reset';
$string['reset_desc'] = 'Reset the shop';
$string['resetbills'] = 'Clear all bills and purchase information';
$string['resetcatalogs'] = 'Clear all catalogs';
$string['resetcustomers'] = 'Clear customer base';
$string['resetitems'] = 'Items to reset';
$string['results'] = 'Results';
$string['runningbills'] = 'Bills : running';
$string['sales'] = 'Sales:';
$string['salesconditions'] = 'Sales conditions:';
$string['salesmanagement'] = 'Sales management';
$string['salesrole_desc'] = 'People who have such role can operate sales backoffice';
$string['salesrolename'] = 'Sales manager';
$string['salesservice'] = 'Sales service';
$string['saverequs'] = 'Save your product configuration';
$string['scantrace'] = 'Scan merchant trace';
$string['search'] = 'Search';
$string['searchby'] = 'Search by';
$string['searchinbills'] = 'Search in running bills.';
$string['searchincustomers'] = 'Search within customer accounts.';
$string['searchinproductinstances'] = 'Search and manage instances of products.';
$string['searchinproducts'] = 'Search in products.';
$string['section'] = 'Category:';
$string['seebigger'] = 'See bigger';
$string['sel'] = 'Sel';
$string['selectall'] = 'Select all';
$string['sellerID'] = 'Seller Registrar ID';
$string['selleraddress'] = 'Seller Address (physical)';
$string['sellerbillingaddress'] = 'Seller Billing Address';
$string['sellerbillingcity'] = 'Seller Billing City';
$string['sellerbillingcountry'] = 'Seller Billing Country';
$string['sellerbillingzip'] = 'Seller Billing Zip Code';
$string['sellercity'] = 'Seller City';
$string['sellercountry'] = 'Seller Country';
$string['selleritemname'] = 'Seller Item Name (paypal config)';
$string['sellerlogo'] = 'Seller logo';
$string['sellermail'] = 'Seller Email';
$string['sellermailsupport'] = 'Mail support address';
$string['sellername'] = 'Seller name';
$string['sellerphonesupport'] = 'Phone support number';
$string['sellertestitemname'] = 'Seller Item Name (Sandbox)';
$string['sellerzip'] = 'Seller Zip Code';
$string['sentto'] = 'Test password mail sent to : {$a}';
$string['seoalias'] = 'SEO Alias';
$string['seotitle'] = 'SEO Title';
$string['seokeywords'] = 'SEO Keywords';
$string['seodescription'] = 'SEO Description';
$string['seoalias_help'] = 'An alias used to forge a smarturl. Smart Urls may be forged using categories aliases and product aliases.';
$string['seotitle_help'] = 'An hidden title in HEAD. This is an important item for a good SEO scoring. Product title will be set on product specific pages.
Category titles will be set when browsing in a product category context. By defaults, gets the product name as title but this may not be optimal.';
$string['seokeywords_help'] = 'Keywords seem to be a deprectated technique for search engines. But it is kept as meta-information enhancements.';
$string['seodescription_help'] = 'An hidden description that helps search engines to index and qualify the page. It should not exceed 255 chars and should present main related keywords of the current topic.';
$string['serviceproxykey'] = 'Service proxy key';
$string['set'] = 'Set:';
$string['setid'] = 'Set ID';
$string['settings'] = 'Settings';
$string['shop'] = 'Shop';
$string['shop'] = 'e-Shop';
$string['shopcaption'] = 'Store caption';
$string['shopdescription'] = 'Description de la boutique';
$string['shopinstance'] = 'Shop instance';
$string['shopproductcreated'] = 'Created by shop product postproduction';
$string['shops'] = 'Shops';
$string['shops_help'] = 'Shops are instances of sales service that plays a catalog with some sales settings';
$string['shortname'] = 'Shortname';
$string['showdescriptioninset'] = ' Show description in set';
$string['shownameinset'] = ' Show product name in set';
$string['signin'] = 'Sign in';
$string['slave'] = 'Linked catalog';
$string['slavegroupcannotbeedited'] = 'A slave product group cannot be edited.';
$string['slaveto'] = 'Linked to ';
$string['softdelete'] = 'Inhibit product';
$string['softrestore'] = 'Restore product';
$string['sold'] = 'Sold:';
$string['standalone'] = 'Standalone catalog';
$string['status'] = 'Status';
$string['stock'] = 'Stock:';
$string['symb'] = ' (Currency needs to be set)';
$string['task_cron'] = 'Automated shop actions';
$string['task_weekly_notification'] = 'Weekly notifications on product instances';
$string['task_daily_notification'] = 'Daily notifications on product instances';
$string['tax'] = 'Tax';
$string['taxcode'] = 'Tax code:';
$string['taxes'] = 'Taxes';
$string['taxhelp'] = 'Taxes';
$string['taxhelp_help'] = 'Taxes Help';
$string['tendays'] = 'ten days';
$string['tenunitspix'] = 'Sales pix for ten units pack:';
$string['testmodeactive'] = 'Moodle Shop is in test mode. We do not allow payments at the moment unless admin users for testing purpose.';
$string['testoverride'] = 'Test lock override';
$string['testuser'] = 'Test user';
$string['threemonths'] = 'three months';
$string['thumbnail'] = 'Thumbnail:';
$string['title'] = 'Title';
$string['todate'] = 'To';
$string['total'] = 'Total';
$string['totalprice'] = 'Total amount';
$string['totaltaxed'] = 'Total TI';
$string['totaltaxes'] = 'Taxes (total)';
$string['totaluntaxed'] = 'Total WT';
$string['tracescan'] = 'Scann';
$string['tracescan_desc'] = 'Grep in trace for a single transaction';
$string['transactionid'] = 'Transaction ID';
$string['ttc'] = 'TTC';
$string['tvaeurope'] = 'VAT European Intracommunautary';
$string['type'] = 'Type';
$string['unit'] = 'Unit. (HT)';
$string['unitpix'] = 'Sales unit pix:';
$string['unitprice1'] = 'Unit price (Range 1):';
$string['unitprice2'] = 'Unit price (Range 2):';
$string['unitprice3'] = 'Unit price (Range 3):';
$string['unitprice4'] = 'Unit price (Range 4):';
$string['unitprice5'] = 'Unit price (Range 5):';
$string['unittests'] = 'Test products';
$string['unity'] = 'Unity';
$string['unitycost'] = 'Unit cost';
$string['unlockcatalogs'] = 'Unlock catalog deletion';
$string['unselectall'] = 'Unselect all';
$string['unset'] = '-- Unset --';
$string['until'] = 'till to';
$string['usedelegation'] = 'Use sales delegation';
$string['userdiscountagreed'] = 'User discount lvl 1';
$string['userdiscountagreed2'] = 'User discount lvl 2';
$string['userdiscountagreed3'] = 'User discount lvl 3';
$string['userenewableproducts'] = 'Uses renewable products';
$string['userenrol'] = 'User enrol';
$string['useshipping'] = 'Uses shipping';
$string['useslavecatalogs'] = 'Uses master/slave catalogs';
$string['usinghandler'] = 'Using handler {$a}';
$string['vendorinfo'] = 'Information about vendor\'s identity';
$string['warning'] = 'Warning:';

$string['privacy:metadata:shop_customer:firstname'] = 'The customer\'s first name';
$string['privacy:metadata:shop_customer:lastname'] = 'The customer\'s last name';
$string['privacy:metadata:shop_customer:hasaccount'] = 'If this customer identity has a moodle account';
$string['privacy:metadata:shop_customer:email'] = 'The customer\'s email';
$string['privacy:metadata:shop_customer:address'] = 'the customer\'s physical address';
$string['privacy:metadata:shop_customer:zip'] = 'The customer\'s zip code';
$string['privacy:metadata:shop_customer:city'] = 'The customer\'s city';
$string['privacy:metadata:shop_customer:country'] = 'the customer\'s country';
$string['privacy:metadata:shop_customer:organisation'] = 'The customer\'s organisation he is purchasing for';
$string['privacy:metadata:shop_customer:invoiceinfo'] = 'Several invoicing info such as the billing address';
$string['privacy:metadata:shop_customer:timecreated'] = 'When this customer record was created';
$string['privacy:metadata:shop_customer'] = 'Personal information about a customer';

$string['privacy:metadata:customer_ownership:userid'] = 'The owner identifier';
$string['privacy:metadata:customer_ownership:customerid'] = 'The customer identifier';
$string['privacy:metadata:customer_ownership'] = 'Information about who the customer is on behalf of';

$string['privacy:metadata:shop_bill:shopid'] = 'The identifier of the shop instance where the purchase was done';
$string['privacy:metadata:shop_bill:userid'] = 'The moodle user id owning the bill';
$string['privacy:metadata:shop_bill:idnumber'] = 'The external identification number (accounting system compatible)';
$string['privacy:metadata:shop_bill:ordering'] = 'The order number of the bill';
$string['privacy:metadata:shop_bill:customerid'] = 'The identifier of the customer record that has purchased';
$string['privacy:metadata:shop_bill:invoiceinfo'] = 'The invoicing information givin by the customer at the instant of purchase';
$string['privacy:metadata:shop_bill:title'] = 'The title of the invoice';
$string['privacy:metadata:shop_bill:worktype'] = 'The type of production required';
$string['privacy:metadata:shop_bill:status'] = 'The statefull state of the purchase';
$string['privacy:metadata:shop_bill:remotestatus'] = 'The remote payement system status, when available';
$string['privacy:metadata:shop_bill:emissiondate'] = 'The date of purchase';
$string['privacy:metadata:shop_bill:lastactiondate'] = 'The date of the last operation on the bill';
$string['privacy:metadata:shop_bill:assignedto'] = 'The id of an internal moodle user the bill is assigned to review or monitor';
$string['privacy:metadata:shop_bill:timetodo'] = 'An indication of the work time needed to build the products (not used)';
$string['privacy:metadata:shop_bill:untaxedamount'] = 'The total amount without taxes';
$string['privacy:metadata:shop_bill:taxes'] = 'The total of dued taxes';
$string['privacy:metadata:shop_bill:amount'] = 'The total amount tax included';
$string['privacy:metadata:shop_bill:currency'] = 'The current currency of the invoice';
$string['privacy:metadata:shop_bill:convertedamount'] = 'The converted amount (seller currency)';
$string['privacy:metadata:shop_bill:transactionid'] = 'The unique transaction id';
$string['privacy:metadata:shop_bill:onlinetransactionid'] = 'The remote payment system transaction id if available';
$string['privacy:metadata:shop_bill:expectedpaiment'] = 'The amount to be paied (not yet used)';
$string['privacy:metadata:shop_bill:paiedamount'] = 'The amount having been payed (not yet used)';
$string['privacy:metadata:shop_bill:paymode'] = 'The payment method';
$string['privacy:metadata:shop_bill:ignoretax'] = 'If the current purchase is free of tax or not';
$string['privacy:metadata:shop_bill:productiondata'] = 'The data collected to build the products';
$string['privacy:metadata:shop_bill:paymentfee'] = 'An amount of paied back fee the seller need to pay to the payment system';
$string['privacy:metadata:shop_bill:productionfeedback'] = 'The feedback received from production process';
$string['privacy:metadata:shop_bill'] = 'All the information about a purchase';

$string['privacy:metadata:shop_bill_item:billid'] = 'The purchase id the bill item belongs to';
$string['privacy:metadata:shop_bill_item:ordering'] = 'The order of the item in the bill';
$string['privacy:metadata:shop_bill_item:type'] = 'The type of the item';
$string['privacy:metadata:shop_bill_item:itemcode'] = 'The catalog item code';
$string['privacy:metadata:shop_bill_item:catalogitem'] = 'The catalog item id';
$string['privacy:metadata:shop_bill_item:abstract'] = 'The short abstract of the product at the instant of purchase';
$string['privacy:metadata:shop_bill_item:description'] = 'The description of the product at the instant of purchase';
$string['privacy:metadata:shop_bill_item:delay'] = 'The delay to produce or deliver (not used)';
$string['privacy:metadata:shop_bill_item:unitcost'] = 'The unit cost of the item at the instant of purchase';
$string['privacy:metadata:shop_bill_item:quantity'] = 'the ordered quantity';
$string['privacy:metadata:shop_bill_item:totalprice'] = 'The total calculated price';
$string['privacy:metadata:shop_bill_item:taxcode'] = 'The id of the applied tax';
$string['privacy:metadata:shop_bill_item:bundleid'] = 'The bundle the item belongs to';
$string['privacy:metadata:shop_bill_item:customerdata'] = 'The information that was asked to the customer to tune the product';
$string['privacy:metadata:shop_bill_item:productiondata'] = 'The information that was collected by the purchase process on build';
$string['privacy:metadata:shop_bill_item'] = 'Information about individual elements of a purchase';

$string['privacy:metadata:shop_product:customerid'] = 'The identifier of the custommer account';
$string['privacy:metadata:shop_product:catalogitemid'] = 'The reference to the catalog definition of the product';
$string['privacy:metadata:shop_product:initialbillitemid'] = 'the first bill item that updated the product';
$string['privacy:metadata:shop_product:currentbillitemid'] = 'the last bill item that updated the product';
$string['privacy:metadata:shop_product:contexttype'] = 'The moodle context level the product is related to';
$string['privacy:metadata:shop_product:instanceid'] = 'The moodle internal instance id the product is related to';
$string['privacy:metadata:shop_product:startdate'] = 'The start date of the product';
$string['privacy:metadata:shop_product:enddate'] = 'The peremption date of the product';
$string['privacy:metadata:shop_product:reference'] = 'A unique product reference identifier';
$string['privacy:metadata:shop_product:productiondata'] = 'Some metadata giving an image of how was built the product';
$string['privacy:metadata:shop_product:extradata'] = 'Some additional information the product has';
$string['privacy:metadata:shop_product:deleted'] = 'If the product has been deleted';
$string['privacy:metadata:shop_product'] = 'Information about product instances owned by the customer';

$string['privacy:metadata:shop_product_event:productid'] = 'The product instance the event is related to';
$string['privacy:metadata:shop_product_event:billitemid'] = 'The bill item the event relates to';
$string['privacy:metadata:shop_product_event:eventtype'] = 'Type of event';
$string['privacy:metadata:shop_product_event:eventdata'] = 'Metadata related to the event';
$string['privacy:metadata:shop_product_event:datecreated'] = 'When the event occured';
$string['privacy:metadata:shop_product_event'] = 'Life cycle event related to a purchased product instance';


$string['noproducts'] = "
<h3>Empty catalog</h3>
<p>The product line is empty.
";

$string['countryrestrictions_help'] = '
<p>You can restrict the country choice list by giving the list of official country codes to use.</p>
';

$string['lettering_help'] = '
<p>Lettering maps online invoices to internal accountance legal numbers.</p>
';

$string['buy_instructions_tpl'] = '
You can choose you pay mode. Click on the choosen payment method and let the procedure guide you through the process:
';

$string['customer_welcome_tpl'] = '
<h3>Welcome to your Customer Account</h3>
<p>Tou may access to all information related to you. You may follow the status of your bills or orderings and ask for support.</p>
';

$string['delete_catalog_dialog_tpl'] = '
Are you sure to destroy this catalogue?\n
Master catalogs : All the slave catalog instances will also be destroyed.
';

$string['empty_bill_tpl'] = '
<h3>Empty bill</h3>
<p>This bill ha no items. You can manually add items to this bill using the "Add bill item" link.</p>
';

$string['empty_taxes_tpl'] = '
<h3>None of the items of this order have tax.</h3>
<p>Taxes can be disabled on item scope.</p>.
';

$string['no_bill_attachements_tpl'] = '
<h5>No bill attachements</h5>
<p>You may attach documents to this bill. Use the link below. You may also attach one (unique) document to each bill item. Use the attach icon (<img src="images/icons/attach.gif">) on the item order line. </p>
';

$string['no_bills_in_account_tpl'] = '
<h3>No bills</h3>
<pThere is no bills in this account.</p>
';

$string['no_categories_tpl'] = '
<p>No categories defined in the catalog.</p>
';

$string['no_orders_in_account_tpl'] = '
<h3>No orders</h3>
<p>You may manually add an order using the "Add order" link beneath.</p>
';

$string['no_products_in_set_tpl'] = '
<p>Empty set</p>
';

$string['no_products_tpl'] = '
<h3>Empty catalog</h3>
<p>This sales catalog has no produt available.</p>
';

$string['upload_text_tpl'] = '
<h5>Zone de téléchargement</h5>
<p>Choisissez un fichier sur votre poste de travail par le bouton "Parcourir". Validez l\'envoi. Vous pouvez être confrontés à des limites de taille.
';

$string['no_product_shippings_tpl'] = '
<h3>Product Shipping</h3>
<p>No shipping extra costs defined for this product.</p>
';

$string['no_zones_tpl'] = '
<h3>Shipping zones</h3>
<p>No shipping zones defined. </p>
';

$string['out_zone_euro_advice_tpl'] = '
<b>Beware :</b> check payment nor swift payment can be accepted out of Euro zone. Thank you for your comprehension.</p>
';

$string['post_billing_message_tpl'] = '
thanks you for your purchase. The order is actually pending for your payement... <br/><br/>
';

$string['search_bill_failed_tpl'] = '
<h3 class="error">Search error</h3>
<p>No bill matches your search criteria: </p>
<p><code>Searching by: <?php echo $by ?> with value : <?php echo $$by ?></code></p>
<p>Please change your search parameters.</p>
';

$string['search_product_failed_tpl'] = '
<h3 class="error">Search error</h3>
<p>No product matches your search criteria: </p>
<p><code>Searching by: <?php echo $by ?> with value : <?php echo $$by ?></code></p>
<p>Please change your search parameters.</p>
';

$string['transaction_confirm_tpl'] = '
<h3><%%SELLER%%> : Purchase Confirmation</h3>
<p>The order from : <br/>
Firstname : <%%FIRSTNAME%%><br/>
Lastname : <%%LASTNAME%%><br/>
Mail : <%%MAIL%%><br/>
From : <%%CITY%%> (<%%COUNTRY%%>)<br/>
<br/>
Has been confirmed as TID : <%%TRANSACTION%%><br/>
using paymode : <%%PAYMODE%%>
<hr/>
<b>Order content :</b><br/>
Items : <%%ITEMS%%> purchased fro total value : <%%AMOUNT%%><br/>
Taxes : <%%TAXES%%><br/>
Total taxed amount : <%%TTC%%>
<hr/>
Access to online ordering <a href=\"<%%SERVER_URL%%>/login/index.php?ticket=<%%TICKET%%>\">here</a>
';

$string['upload_failure_tpl'] = '
<h5>Uploading error</h5>
<p>Uploading failed for soem reason</p>
';

$string['upload_success_tpl'] = '
<h5>Upload success</h5>
<p>File is now attached. Use the above link if your screen is not refreshed in the next 5 seconds.</p>
';

$string['bill_complete_text_tpl'] = '
This order has been saved.<br/>
';

$string['discountrate_help'] = '
A discount rate applied on the overall amount. May be applied inconditionally if customer has the local/shop:discountagreed capability on
';

$string['discountthreshold_help'] = '
A threshold that triggers the effectivity of the discount
';

$string['discountrate2_help'] = '
A discount rate applied when customer has the local/shop:seconddiscountagreed capability on
';

$string['discountrate3_help'] = '
A discount rate applied when customer has the local/shop:thirddiscountagreed capability on
';

$string['resetguide'] = 'This service allows resetting all or part of data within the shop. Some values
are linked: f.e.<br/><ul><li>When deleting the custome base, you will also erase all bills</li><li>When
deleting all catalogues, you may keep the customer base, but all billing information will be removed.</li></ul>';

$string['quantaddressesusers_help'] = 'Products that use this option will affect the user input stack
at the second step of the shopping process';

$string['editshopsettings_desc'] = 'This version provides a single shop instance. You can edit the settings
of the shop here.';

$string['eula_help'] = 'Please read and validate the following End User Licence Agreement before any purchase
on {$a}. Confirming the form will provide acceptance of the herein conditions.';

$string['categoryowner_desc'] = 'The Category Owner owns a category as course creator and can manage the
category and courses within it. He can nominate teachers or course creators and is legally responsible.';

global $CFG;
require($CFG->dirroot.'/local/shop/lang/en/front.php');
require($CFG->dirroot.'/local/shop/lang/en/catalogs.php');
require($CFG->dirroot.'/local/shop/lang/en/discounts.php');
require($CFG->dirroot.'/local/shop/lang/en/shops.php');
require($CFG->dirroot.'/local/shop/lang/en/bills.php');
require($CFG->dirroot.'/local/shop/lang/en/partner.php');
require($CFG->dirroot.'/local/shop/lang/en/products.php');
require($CFG->dirroot.'/local/shop/lang/en/purchasemanager.php');
require($CFG->dirroot.'/local/shop/lang/en/customers.php');
require($CFG->dirroot.'/local/shop/lang/en/tax.php');
require($CFG->dirroot.'/local/shop/lang/en/shipzones.php');
require($CFG->dirroot.'/local/shop/lang/en/pdf.php');

if ('pro' == local_shop_supports_feature()) {
    include($CFG->dirroot.'/local/shop/pro/lang/en/pro.php');
}

// Currencies.

$string['currency'] = 'Currency';
$string['EUR'] = 'Euro';
$string['CHF'] = 'Swiss franc';
$string['USD'] = 'US dollar';
$string['CAD'] = 'Canadian dollar';
$string['AUD'] = 'Australian dollar';
$string['GPB'] = 'English pound';
$string['TRY'] = 'Turkish pound';
$string['PLN'] = 'Zloty (Poland)';
$string['RON'] = 'Roman leu';
$string['ILS'] = 'Shekel';
$string['KRW'] = 'Won (corea)';
$string['JPY'] = 'Yen (japan)';
$string['TND'] = 'Dinar (Tunisian, internal market)';
$string['MAD'] = 'Dinar (Marocco, internal market)';

$string['EURsymb'] = '&euro;';
$string['CHFsymb'] = 'CHF';
$string['USDsymb'] = '$ (US)';
$string['CADsymb'] = '$ (CA)';
$string['AUDsymb'] = '$ (AU)';
$string['GPBsymb'] = '£';
$string['TRYsymb'] = '£ (TK)';
$string['PLNsymb'] = 'Zl';
$string['RONsymb'] = 'Leu';
$string['ILSsymb'] = 'Sh';
$string['KRWsymb'] = 'Won (corea)';
$string['JPYsymb'] = 'Yen (japan)';
$string['TNDsymb'] = 'Dn (TU)';
$string['MADsymb'] = 'Dn (MA)';

include(__DIR__.'/pro_additional_strings.php');