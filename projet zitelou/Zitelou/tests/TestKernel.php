<?php

namespace App\Tests;

use App\Kernel;

/**
 * Kernel de test distinct pour éviter redéclaration lors de boot multiple.
 */
class TestKernel extends Kernel {}
