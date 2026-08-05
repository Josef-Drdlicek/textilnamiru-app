<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Feature testy potřebují ceník (proměnné, stuhy, pásma rolet).
     */
    protected $seed = true;
}
