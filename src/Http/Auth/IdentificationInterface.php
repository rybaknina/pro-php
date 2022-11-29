<?php

namespace Nin\ProPhp\Http\Auth;

use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Http\Request;

interface IdentificationInterface
{
    public function user(Request $request): User;
}