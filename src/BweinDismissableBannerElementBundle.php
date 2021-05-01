<?php

declare(strict_types=1);

/*
 * This file is part of Dismissable Banner Element for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\DismissableBannerElement;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BweinDismissableBannerElementBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
