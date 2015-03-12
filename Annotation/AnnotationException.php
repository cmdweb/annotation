<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 05/12/2014
 * Time: 20:30
 */

namespace Helpers\Annotation;


class AnnotationException extends \Exception{

    function __construct($msg, $code = 0){
        parent::__construct($msg, $code);
    }

} 