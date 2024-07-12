<?php
/*   __________________________________________________
    |              on 2.0.12              |
    |__________________________________________________|
*/
 namespace local_shop; defined("\115\117\x4f\x44\114\x45\137\x49\116\124\105\122\116\x41\114") || die; require_once $CFG->dirroot . "\x2f\154\157\143\x61\x6c\57\x73\150\157\x70\57\x70\x72\x6f\x2f\x63\154\141\x73\163\145\x73\57\x44\151\163\143\157\165\156\164\x2e\x63\154\141\163\163\56\x70\150\x70"; use StdClass; class UnconditionalDiscount extends Discount { public function check_applicability(&$qQ0hv = null) { goto qoAWJ; qoAWJ: $this->checked = true; goto q01J_; oSdB7: $syuG8->ratio = $this->ratio; goto jENUX; q01J_: $syuG8 = new StdClass(); goto JoW4f; JoW4f: $syuG8->code = "\111\116\x43" . $this->id; goto oSdB7; euOqD: return true; goto hio8o; jENUX: $this->productiondata = $syuG8; goto euOqD; hio8o: } public function preview_applicability() { return true; } public function interactive_form() { goto cBHa2; cBHa2: $uBLTU = new Stdclass(); goto V1C6R; V1C6R: $uBLTU->label = $this->argument; goto fHnlY; fHnlY: return $uBLTU; goto Q8k91; Q8k91: } }
