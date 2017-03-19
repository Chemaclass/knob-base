<?php

namespace Knob\Repository;

use Knob\Models\User;

interface UserRepository
{
    /**
     * @return User
     */
    public function getCurrent();
}