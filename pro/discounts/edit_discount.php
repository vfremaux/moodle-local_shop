<?php
/*   __________________________________________________
    |              on 2.0.12              |
    |__________________________________________________|
*/
 require "\56\x2e\x2f\x2e\56\57\56\56\x2f\56\x2e\x2f\x63\x6f\156\x66\151\x67\x2e\160\x68\x70"; require_once $CFG->dirroot . "\57\x6c\x6f\143\x61\154\57\163\x68\157\x70\x2f\154\157\143\x61\x6c\154\151\142\x2e\160\150\x70"; require_once $CFG->dirroot . "\x2f\154\157\143\x61\x6c\x2f\163\150\157\160\57\x70\x72\x6f\x2f\x66\x6f\x72\155\163\57\x66\157\162\x6d\x5f\x64\151\163\x63\x6f\x75\156\164\56\x63\x6c\141\x73\x73\x2e\160\x68\160"; require_once $CFG->dirroot . "\57\x6c\157\143\x61\x6c\x2f\163\150\157\160\57\154\151\x62\56\160\x68\160"; require_once $CFG->dirroot . "\57\154\157\x63\141\x6c\57\x73\x68\x6f\x70\57\160\x72\157\x2f\143\x6c\141\x73\x73\x65\x73\x2f\x44\151\163\x63\x6f\x75\x6e\x74\56\x63\154\x61\x73\x73\56\160\x68\160"; use local_shop\Discount; goto DqroR; cHDQc: $nmDV_ = new moodle_url("\x2f\154\x6f\143\141\x6c\57\x73\x68\x6f\x70\x2f\x70\x72\157\x2f\144\151\x73\x63\x6f\x75\x6e\164\163\57\145\x64\151\164\137\144\x69\163\x63\157\165\x6e\x74\x2e\160\x68\160", array("\x64\151\x73\x63\x6f\165\x6e\164\151\x64" => $ptVwY)); goto zD79b; oIhA3: $PAGE->navbar->add(get_string("\163\141\154\145\x73\163\145\x72\x76\x69\143\145", "\154\157\x63\x61\x6c\x5f\x73\x68\x6f\x70"), new moodle_url("\57\x6c\x6f\x63\x61\x6c\57\163\150\x6f\160\x2f\151\x6e\144\x65\x78\x2e\x70\150\x70")); goto pUpcy; pUpcy: $PAGE->navbar->add(get_string("\145\x64\151\164\144\151\163\x63\x6f\x75\156\x74", "\x6c\157\x63\141\x6c\137\x73\x68\157\x70")); goto QQY1C; jjiYy: $DJiGh->set_data($nfJtv); goto h1Pur; poA5H: include_once $CFG->dirroot . "\57\x6c\x6f\x63\x61\x6c\57\163\150\x6f\160\57\x70\x72\157\57\144\x69\163\x63\x6f\165\x6e\164\163\57\x64\151\163\x63\x6f\165\x6e\x74\x73\56\143\157\156\x74\162\x6f\x6c\154\x65\162\x2e\x70\150\160"; goto gWCFl; NnFca: $DJiGh->set_data($YYLBx); goto OXZhN; NlJqX: ALUWr: goto kN9qA; B5L0r: $DJiGh = new Discount_Form(null, $eRMp0); goto qt9da; OXZhN: Ffyt_: goto FmqwJ; EdF2M: redirect(new moodle_url("\x2f\154\x6f\143\141\154\57\x73\150\157\160\x2f\x70\x72\157\x2f\144\x69\163\x63\157\x75\x6e\164\x73\x2f\166\x69\x65\x77\56\x70\150\x70", ["\166\x69\x65\167" => "\x76\151\145\167\x41\x6c\154\104\x69\163\x63\157\x75\156\x74\163"])); goto rcM5m; kN9qA: if (!$DJiGh->is_cancelled()) { goto a52Ft; } goto EdF2M; GYJta: $PAGE->set_heading(get_string("\160\x6c\165\147\x69\x6e\156\141\x6d\x65", "\154\157\143\x61\x6c\137\163\150\x6f\x70")); goto oIhA3; qt9da: goto ALUWr; goto AAGku; h1Pur: goto Ffyt_; goto y02Nz; rh0IF: if (!($lWyz5 = $DJiGh->get_data())) { goto JHpWH; } goto poA5H; z8ARN: $SA3Fw = Discount::instance($ptVwY); goto V6z0T; g9dzZ: $ptVwY = optional_param("\x64\x69\163\143\157\165\156\164\151\x64", '', PARAM_INT); goto MFoGl; V6z0T: $YYLBx = clone $SA3Fw->record; goto NnFca; rcM5m: a52Ft: goto rh0IF; QQY1C: if ($ptVwY) { goto dy9eB; } goto F9Zpq; gWCFl: $JhbiS = new \local_shop\backoffice\discounts_controller(); goto K2C00; rAEOC: redirect(new moodle_url("\x2f\154\157\143\x61\154\x2f\163\150\157\x70\x2f\x70\x72\157\x2f\144\x69\x73\x63\157\x75\156\164\163\57\x76\x69\145\167\56\160\150\x70", ["\166\x69\145\x77" => "\166\x69\x65\167\101\154\x6c\x44\151\163\x63\x6f\165\x6e\164\163"])); goto ykQ3w; Hf84N: if ($ptVwY) { goto Q6MC3; } goto B0HFP; jjqoS: $PAGE->set_context($AO7zv); goto TsRAk; DqroR: list($dA9EC, $DfZgU, $paYaA) = shop_build_context(); goto g9dzZ; PUSmI: $JhbiS->process("\x65\x64\151\164"); goto rAEOC; y02Nz: Q6MC3: goto xwEYf; nekZN: require_login(); goto uMpd3; MFoGl: $AO7zv = context_system::instance(); goto nekZN; d2Hpn: $DJiGh = new Discount_Form(null, $eRMp0); goto NlJqX; nEb0f: $nfJtv->shopid = $dA9EC->id; goto jjiYy; xwEYf: $DJiGh = new Discount_Form('', ["\x77\150\x61\164" => "\145\144\x69\x74", "\164\150\145\x63\141\x74\141\154\x6f\147" => $DfZgU]); goto z8ARN; FmqwJ: echo $OUTPUT->header(); goto FlNqq; zD79b: $PAGE->set_url($nmDV_); goto jjqoS; io5xM: $nfJtv = new StdClass(); goto nEb0f; K2C00: $JhbiS->receive("\145\144\x69\x74", $lWyz5, $DJiGh); goto PUSmI; uMpd3: require_capability("\154\157\x63\x61\x6c\x2f\x73\x68\x6f\x70\x3a\163\141\154\145\163\x61\144\155\x69\x6e", $AO7zv); goto cHDQc; F9Zpq: $eRMp0 = ["\x77\x68\141\164" => "\x61\x64\x64", "\x74\150\x65\143\141\164\141\x6c\157\x67" => $DfZgU]; goto B5L0r; TsRAk: $PAGE->set_title(get_string("\160\x6c\x75\147\x69\x6e\x6e\x61\155\x65", "\154\x6f\x63\141\x6c\137\x73\150\x6f\160")); goto GYJta; ZcimL: $eRMp0 = ["\x77\x68\x61\164" => "\x65\x64\x69\164", "\x74\150\145\143\141\x74\x61\x6c\x6f\x67" => $DfZgU]; goto d2Hpn; B0HFP: $DJiGh = new Discount_Form('', ["\x77\x68\141\x74" => "\x61\x64\144", "\x74\150\145\x63\x61\164\x61\x6c\157\x67" => $DfZgU]); goto io5xM; ykQ3w: JHpWH: goto Hf84N; AAGku: dy9eB: goto ZcimL; FlNqq: $DJiGh->display(); goto UkWjh; UkWjh: echo $OUTPUT->footer();
