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
 * Data for product block in bill.
 *
 * @package     local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop\output;

use StdClass;
use Templatable;
use renderer_base;

/**
 * An objecvt that represents product block data in bills
 */
class bill_product_block implements Templatable {

    /** @var The product to render */
    protected $product;

    /**
     * Base constructor
     * @param Catalogitem $product
     * @param object $theblock instance of shop_access block if known.
     */
    public function __construct(CatalogItem $product, $theblock = null) {
        $this->product = $product;
        $this->theblock = $theblock;
    }

    /**
     * Exporter for template.
     * @param renderer_base $output unused
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function export_for_template(renderer_base $output /* unused */) {

        $template = new StdClass();
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
                $pricetpl = new StdClass();
                $pricetpl->range = $range;
                $pricetpl->price = $price;
                $template->prices[] = $pricetpl;
            }
        }

        $ismax = $product->maxdeliveryquant && $product->maxdeliveryquant == $product->preset;
        $template->disabled = ($ismax) ? 'disabled="disabled"' : '';
        $template->blockid = $this->theblock->instance->id ?? 0;
        $template->jshandler = 'ajax_add_unit('.$this->theblock->id.', \''.$product->shortname.'\')';
        $template->shortname = $product->shortname;
        $template->units = $this->units($product, true);

        return $template;
    }
}
