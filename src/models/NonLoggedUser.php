<?php
/*
 * This file is part of the Knob-base package.
 *
 * (c) José María Valera Reales <chemaclass@outlook.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knob\Models;

/**
 * @author José María Valera Reales
 */
class NonLoggedUser extends User
{
    public function __construct()
    {
        parent::__construct(0);
    }
}