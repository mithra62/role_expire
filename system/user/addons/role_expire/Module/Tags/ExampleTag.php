<?php

namespace Mithra62\RoleExpire\Module\Tags;

use ExpressionEngine\Service\Addon\Controllers\Tag\AbstractRoute;

class ExampleTag extends AbstractRoute
{
    // Example tag: {exp:role_expire:example_tag}
    public function process()
    {
        return "My tag";
    }
}
