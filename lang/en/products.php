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

$string['searchforakeyinstructions'] = '
this key was given to you by a customer as a transaction reference. you can try a search typing the first chars of the key
';
$string['addbundle'] = 'Add bundle';
$string['assets'] = 'Product assets';
$string['addcategory'] = 'Add a product category';
$string['addproduct'] = 'Add product';
$string['addoverride'] = 'Override master definition';
$string['addset'] = 'Add set';
$string['allcategories'] = 'All categories';
$string['automation'] = 'Automation';
$string['behaviour'] = 'Behaviour';
$string['billorderingnumber'] = 'Invoice ordering number';
$string['categories'] = 'Categories';
$string['customersnameonbill'] = 'The customer\'s name on invoice';
$string['deletebundle'] = 'Delete bundle and content';
$string['deletecategory'] = 'Delete a product category';
$string['deleteoverride'] = 'Delete override';
$string['deleteproduct'] = 'Delete product';
$string['deleteset'] = 'Delete set';
$string['editbundle'] = 'Update bundle';
$string['editcategory'] = 'Update a product category';
$string['editproduct'] = 'Update product';
$string['editproductvariant'] = 'Update product (slave override)';
$string['editset'] = 'Update set';
$string['financials'] = 'Financial aspects';
$string['handlerparams'] = 'Handler parameters';
$string['hashandlers'] = 'This product has action handlers';
$string['maxquant'] = 'Q max';
$string['newbundle'] = 'New bundle';
$string['newcategory'] = 'New category';
$string['newitem'] = 'New item';
$string['newproduct'] = 'New product';
$string['newset'] = 'New set';
$string['noproductinbundle'] = 'Bundle is empty';
$string['noproductincategory'] = 'Category is empty';
$string['noproductinset'] = 'Set is empty';
$string['noproducts'] = 'No products';
$string['nocatsslave'] = 'This is a slave catalog. No categories can be edited.';
$string['parentcategory'] = 'Parent category';
$string['rootcategory'] = '-- Root category --';
$string['producteulas'] = 'Product Terms of Use';
$string['productiondata'] = 'Production data';
$string['productpassword'] = 'Product password';
$string['removealllinkedproducts'] = 'Remove all linked products';
$string['removeproductfrombundle'] = 'Remove from bundle';
$string['removeproductfromcatalogue'] = 'Remove product from catalog';
$string['removeproductfromset'] = 'Remove from set';
$string['removeproductinset'] = 'Remove product from set';
$string['removeset'] = 'Remove all set';
$string['renewable'] = 'Can renew';
$string['requiredformatsuccess'] = 'Required data JSON format is ok';
$string['requireddata'] = 'Specific data to pull from customer';
$string['requireddatacaption'] = 'Some of your products will need some configuration to be produced correctly. Please enter the required information in each product instance in the following form before continuing purchase sequence.';
$string['toset'] = 'Convert to set';
$string['tobundle'] = 'Convert to bundle';
$string['toproduct'] = 'Convert back to product';
$string['unlinkproduct'] = 'Unlink product';
$string['unlinkcontent'] = 'Unlink all subproducts';
$string['user_enrolment'] = 'User enrol';
$string['warningcustomersupportcoursedefaultstosettings'] = 'Customer support course defaults to settings';
$string['warningnocustomersupportcourse'] = 'No customer support area defined';
$string['errornocustomersupportcourse'] = 'Customer support course {$a} does not exist';

$string['handlerparams_help'] = '
## Moodle integrated shop

### Purchase handler parameters

Some purchase handlers need some parameters. This field allows the sales backofficer to tune each product by passing a parameter string.
e.g. a hanlder that enrols a student in a paied for course needs to know the course where to enrol the customer.
Some handlers may require mandatory values, and optional params. You can use the "Test products" feature to check the integrity of your
products tunning before publishing them.

The general form of the parameters is an url encoded like string:

   \'param1=value1&param2=value2...\'

Some parameters are common to all generic hanlders:

    \'customersupport=<%courseid%>\'

Will provide the id of the ocurse used for customer support worplace. If not given and not defined in general shop block settings, no
customer support enrolment will be performed.

#### Generic handler: Enrol me to a course

**Settings**

    \'coursename=<%course shortname%>\'

the course shortname as defined by course creator. this is supposed to be unique and always present.

    \'role=<%role shortname%>\'

the role that will be assigned to customer in the ocurse

    \'duration=<%enrol duration in days%>\'

the real start/end dates of the enrol will be calculated from the time the handler is run.

#### Generic handler: Setup a course session

**Settings**

    \'coursename=<%course shortname%>\'

the course shortname as defined by course creator. this is supposed to be unique and always present.

    \'supervisor=<%role shortname%>\'

This allows designating the exact role that will be assigned for supervisors.

    \'duration=<%enrol duration in days%>\'

the real start/end dates of the enrol will be calculated from the time the handler is run.

#### Generic handler: Course creation (Pro version)

**Settings**

    \'template=<%course template shortname%>\'

The shortname of an existing course considered as template for course. The shortname is unique and is always present
in Moodle. Check there is a prepared backup of the course in the ocurse backup area.

    \'coursecategory=<%course category id%>\'

This will tune where the new course will be created. Note that the product owner MUST have course creation capability in
the designated category for the operation to succeed.

    \'duration=<%duration in days%>\'

Duration is calculated from the purchase date and affects the teacher account enrol and is stored in the product
metadata stub to calculate product obsolescence and trigger end of life action.

#### Generic handler: Course category creation (Pro version)

**Settings**

    \'parentcategory=<%categoryid%>\'

The parent category to which the created category will be attached to. The product owner needs to be manager of the category
or have the relevant permissions.

#### Generic handler: Assign a role in a context

**Settings**

   \'contextlevel=<%contextlevelID%>\'   (10 = system, 40 = category, 50 = course, 70 = module, 80 = block)

The context level.

    \'instance=<%instanceID%>\'

The ID of the instance attached to the context. Irrelevant for system context.

    \'role=<%roleshortname%>\'

the shortname of the role to assign. A capability check will be performed on product owner to actually execute the product handler.
';

$string['renewable_help'] = '
When a product is set as renewable, it may be defined with a product duration from the purchase date. This should be handled
by the product purchase handler and setup through an internal handler parameter. All handlers do not support duration. When enabled,
the customer account interfaces will support product end of period notification, and purchasing agin the product with a reference code
will extend the period on the same product instance.
';

$string['producteulas_help'] = 'Eulas for each purchased product will be aggregated to general shop eulas into a panel that will need
pre-order validation.
';

$string['requireddata_help'] = 'some hanlders need some data to be requested from the customer for each instance.

this required data uses a JSON format for defining an array of form widgets that will need to be pused to the
front office.

The object expressions adopt the following structure :

    array(
        \'p0\': array(\'field\' => \'the_field_name\',
              \'label\' => \'some visible name\',
              \'type\' => \'textfield\',
              \'desc\' => \'some desc\',
              \'attrs\' => array(\'size\' => 80)),
         \'p1\': array(\'field\' => \'description_sample\',
               \'label\' => \'Description (sample)\',
               \'type\' => \'textarea\',
               \'desc\' => \'Short Description (sample)\'),
         \'p2\': array(\'field\' => \'template_sample\',
               \'label\' => \'Model (sample)\',
               \'type\' => \'select\',
               \'desc\' => \'Course template (sample)\',
               \'options\' => array(\'MOD1\' => \'Model1\', \'MOD2\' => \'Model2\')));

the resuting expression is :

[{"field":"the_field_name","label":"some visible name","type":"textfield","desc":"some desc","attrs":{"size":80}},
 {"field":"description_sample","label":"Description (sample)","type":"textarea","desc":"Short Description (sample)"},
 {"field":"template_sample","label":"Model (sample)","type":"select","desc":"Course template (sample)",
           "options":{"MOD1":"Model1","MOD2":"Model2"}}]

You may use an online service such as http://www.objgen.com/json to help you generating the
appropriate syntax.
';

$string['productiondata_help'] = 'Some additional data that will be used in the production cycle and that the product handlers or processing will need.
Some data may affect additional optional shop processings such as license key verification (based on a "component" identification. See detailed doc)
Enter a json encoded array of mapped values.';

