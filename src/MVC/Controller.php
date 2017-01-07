<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil\MVC;

use \Soil\Application;

abstract class Controller
{
    protected $app = null;
    protected $view = null;
    protected $twig = null;


    public function setApp(\Soil\Application $app)
    {
        $this->app = $app;
        $this->view = $app['views'];
        $this->twig = $app['twig'];
    }
}
