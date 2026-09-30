<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

/**
 * Minimal stub of Laminas' AbstractHelper so theme view helpers can be
 * instantiated in unit tests without pulling the full laminas-view package.
 */
abstract class AbstractHelper
{
    /** @var mixed */
    protected $view;

    /**
     * @param mixed $view
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getView()
    {
        return $this->view;
    }
}
