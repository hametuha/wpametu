<?php

namespace WPametu;

/**
 * Base class for all class
 * 
 * @author Takahashi Fumiki
 * @since 0.1
 */
abstract class Base extends Pattern\Singleton
{

	use Traits\i18n,
        Traits\Util{
        __get as traitGet;
    };

    /**
     * Model to use
     *
     * @var array
     */
    protected $models = [];

    /**
     * Construcor
     *
     * @param array $arguments
     */
    final protected function __construct(array $arguments){
        $this->initialized();
    }

    /**
     * Executed at the end of constructor
     *
     * Override this function to constructor
     */
    protected function initialized(){

    }


    private function seekTable(){

    }

    private function seekModel(){

    }
}
