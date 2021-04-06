<?php

namespace Technovistalimited\Notific\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Notific Facade Class.
 *
 * @category   Facade
 * @package    Laravel
 * @subpackage TechnoVistaLimited/Notific
 * @author     Mayeenul Islam <islam.mayeenul@gmail.com>
 * @license    MIT (https://opensource.org/licenses/MIT)
 * @link       https://github.com/technovistalimited/notific/
 */
class Notific extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'notific';
    }
}
