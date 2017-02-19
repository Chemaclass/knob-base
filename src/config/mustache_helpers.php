<?php
/*
 * This file is part of the Knob-base package.
 *
 * (c) José María Valera Reales <chemaclass@outlook.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Config;

use Knob\I18n\I18n;
use Knob\Libs\Utils;

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
$i18n = new I18n(new Utils(APP_DIR, [
    Utils::AVAILABLE_LANGUAGES => [
        Utils::LANG_KEY => Utils::LANG_VALUE,
    ],
    Utils::DEFAULT_LANGUAGE => Utils::DEFAULT_LANG,
    Utils::DEFAULT_LANGUAGE_FILE => Utils::DEFAULT_LANG_FILE,
]));

return [
    'trans' => function ($value) use ($i18n) {
        return $i18n->trans($value);
    },
    'transu' => function ($value) use ($i18n) {
        return $i18n->transu($value);
    }
];
