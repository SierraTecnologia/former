<?php
namespace Former;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Dispatch calls from Former to the different
 * form creators like Form, Actions, Elements and others
 */
class MethodDispatcher
{
    /**
     * The IoC Container
     *
     * @var Container
     */
    protected $app;

    /**
     * An array of fields repositories
     *
     * @var array
     */
    protected $repositories = array();

    /**
     * Build a new Dispatcher
     *
     * @param Container $app
     * @param array     $repositories
     */
    public function __construct(Container $app, $repositories)
    {
        $this->app          = $app;
        $this->repositories = (array) $repositories;
    }

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// REPOSITORIES /////////////////////////
    ////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// DISPATCHERS //////////////////////////
    ////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// HELPERS //////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the correct class to call according to the created field
     *
     * @param string $method The field created
     *
     * @return string The correct class
     */
    protected function getClassFromMethod($method)
    {
        // If the field's name directly match a class, call it
        $class        = Str::singular(Str::title($method));
        $studly_class = Str::singular(Str::studly($method));
        foreach ($this->repositories as $repository) {
            if (class_exists($repository.$studly_class)) {
                return $repository.$studly_class;
            } else {
                if (class_exists($repository.$class)) {
                    return $repository.$class;
                }
            }
        }

        // Else convert known fields to their classes
        switch ($method) {
        case 'submit':
        case 'link':
        case 'reset':
            $class = Former::FIELDSPACE.'Button';
            break;

        case 'multiselect':
            $class = Former::FIELDSPACE.'Select';
            break;

        default:
            $class = Former::FIELDSPACE.'Input';
            break;
        }

        return $class;
    }
}
