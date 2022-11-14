<?php

namespace Nin\ProPhp\Http\Actions;

use Nin\ProPhp\Http\Request;
use Nin\ProPhp\Http\Response;

interface ActionInterface
{
    public function handle(Request $request): Response;
}