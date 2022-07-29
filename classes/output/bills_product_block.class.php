<?php
<<<<<<< HEAD
=======
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
>>>>>>> MOODLE_40_STABLE

namespace local_shop\output;

defined('MOODLE_INTERNAL') || die();

class bill_product_block implements \Templatable {

    protected $product;

    public function __construct($product) {
        $this->product = $product;
    }

    public function export_for_template($output) {

        $template = new \StdClass;
        $template->imageurl = $product->get_image_url();
        $template->name = $product->name;
        $template->description = format_text($product->description);
        $template->code = $product->code;
        $prices = $product->get_printable_prices(true);
        $template->currency = $product->currency;
        if (count($prices) <= 1) {
            $template->singleprice = true;
            $template->price = array_shift($prices);
        } else {
            $template->singleprice = false;
            foreach ($prices as $range => $price) {
                $pricetpl = new \StdClass;
                $pricetpl->range = $range;
                $pricetpl->price = $price;
                $template->prices[] = $pricetpl;
            }
        }

        $ismax = $product->maxdeliveryquant && $product->maxdeliveryquant == $product->preset;
        $template->disabled = ($ismax) ? 'disabled="disabled"' : '';
        $template->blockid = 0 + @$this->theblock->instance->id;
        $template->jshandler = 'ajax_add_unit('.$blockid.', \''.$product->shortname.'\')';
        $template->shortname = $product->shortname;
        $template->units = $this->units($product, true);

        return $template;
    }
}