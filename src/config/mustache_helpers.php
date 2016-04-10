<?php
/*
 * This file is part of the Knob-mvc package.
 *
 * (c) José María Valera Reales <chemaclass@outlook.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Config;

use Knob\I18n\I18n;

/**
 * ============================
 * Your Mustache helpers
 * ============================
 *
 * @see knob-base/src/config/mustache_helpers.php -> Parent file
 *     
 *      ----------------------------
 *      For example:
 *      ----------------------------
 *      $lower_text = 'lower text to upper'; // var from PHP code
 *     
 *      {{#case.upper}} lower_text {{/case.upper}} -> LOWER TEXT TO UPPER
 *      Or
 *      {{ lower_text | case.upper}} -> LOWER TEXT TO UPPER
 *     
 *     
 * @link https://github.com/bobthecow/mustache.php#usage
 * @link https://github.com/bobthecow/mustache.php/wiki/FILTERS-pragma
 *      
 */
return [
    'trans' => function ($value) {
        return I18n::trans($value);
    },
    'transu' => function ($value) {
        return I18n::transu($value);
    }
];
