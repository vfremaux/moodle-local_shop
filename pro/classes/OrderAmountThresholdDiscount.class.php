<?php
/*   __________________________________________________
    |              on 2.0.12              |
    |__________________________________________________|
*/
 namespace local_shop; defined("\115\117\x4f\104\x4c\x45\x5f\111\x4e\x54\105\x52\x4e\x41\x4c") || die; require_once $CFG->dirroot . "\x2f\x6c\x6f\x63\141\x6c\57\x73\x68\157\x70\57\x70\162\x6f\x2f\x63\x6c\141\x73\x73\x65\x73\57\x44\151\x73\x63\157\x75\x6e\x74\56\x63\x6c\x61\163\x73\56\160\x68\x70"; use StdClass; class OrderNumDiscount extends Discount { public function check_applicability(&$qQ0hv = null) { goto iZy48; ubVuj: if (!($qQ0hv->amount > $ZAZAt)) { goto QGjSh; } goto Eyb6Q; CUvAS: QGjSh: goto r8r_i; iZy48: $ZAZAt = $this->applydata; goto ubVuj; Eyb6Q: return true; goto CUvAS; r8r_i: return false; goto Gleas; Gleas: } public function preview_applicability() { return false; } }
