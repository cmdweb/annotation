<?php
/**
 * Criado Por: Gabriel Malaquias
 * github: github.com/gmalaquias
 * Date: 30/04/2015
 * Time: 23:26
 */

namespace Alcatraz\Annotation;
use Alcatraz\Cache\Cache;

/**
 * Class Annotation
 * @package Helpers\Annotation
 */


class Annotation {

    /**
     * @type: ReflectionClass()
     */
    private $_reflection;

    /**
     * @type: Generic
     * @description: receives the class used to get annotations
     */
    private $_class;

    /**
     * @type: array
     * @description: receives all the notes read the attributes of the class
     */
    private $_annotations = array();

    /**
     * @type: array
     * @description: receives all the notes read the methods of the class
     */
    private  $_annotationsMethods = array();

    /**
     * @type: string
     * @description: regex used to read the code blocks
     */
    private $_regex = "/@(.*):(.*)|@(.*)/mi";

    /**
     * @type: array
     * @description: receives type annotations
     */
    private $_attributes = array(
        "Required" => array(),
        "NotMapped" => array("getFunction" => false),
        "Range" => array(),
        "Length" => array(),
        "Email" => array(),
        "Date" =>  array(),
        "DateTime" => array(),
        "DisplayName" => array("getFunction" => false),
        "Type" => array("getFunction" => false),
        "PrimaryKey" => array("getFunction" => false),
        "AllowHtml" => array("getFunction" => false),
        "Type" => array("getFunction" => false),
        "Virtual" => array("getFunction" => false),
        "Fk" => array("getFunction" => false),
        "Name" => array("getFunction" => false)
    );

    /**
     * @param: $class Class
     * @throws: AnnotationException
     */
    function __construct($class){
        $this->getClass($class);

        $this->_reflection = new \ReflectionClass($this->_class);

        $cache = new Cache();
        $cacheGet = $cache->get(get_class($this->_class));
        $cacheMethods = $cache->get("methods" . get_class($this->_class));

        if($cacheGet == null) {
            $this->getAllAnnotations();
            $cache->set(get_class($this->_class), $this->_annotations, "24 hours");
            $cache->set("methods" . get_class($this->_class), $this->_annotationsMethods, "24 hours");
        }else{
            $this->_annotations = $cacheGet;
            $this->_annotationsMethods = $cacheMethods;
        }
    }

    /**
     * @description: passes attributes for class attribute by calling the method that reads the blocks
     */
    private function getAllAnnotations(){
        $properties = $this->_reflection->getProperties();

        $methods = $this->_reflection->getMethods();

        foreach ($properties as $l) {
            $this->getAnnotationByAttribute($l->name);
        }

        foreach($methods as $l){
            $this->getAnnotationByMethod($l->name);
        }
    }

    /**
     * @param: $attr Attribute for read
     * @description: handle attribute of the comment block passes the parameter is the instantiated class in the constructor
     */
    private function getAnnotationByAttribute($attr){
        $method = new \ReflectionProperty($this->_class, $attr);

        preg_match_all($this->_regex, $method->getDocComment(),$out, PREG_SET_ORDER);
        #var_dump($this->_attributes);

        $this->_annotations[$attr] = array();

        if(is_array($out)) :
            $count = count($out);

            for ($i = 0; $i < $count; ++$i):
                $this->setAnnotation($out[$i], $attr);
            endfor;
        endif;
    }

    /**
     * @param: $attr Attribute for read
     * @description: handle attribute of the comment block passes the parameter is the instantiated class in the constructor
     */
    private function getAnnotationByMethod($attr){
        $method = new \ReflectionMethod($this->_class, $attr);

        preg_match_all($this->_regex, $method->getDocComment(),$out, PREG_SET_ORDER);
        #var_dump($this->_attributes);

        $this->_annotationsMethods[$attr] = array();

        if(is_array($out)) :
            $count = count($out);

            for ($i = 0; $i < $count; ++$i):
                $this->setAnnotationMethod($out[$i], $attr);
            endfor;
        endif;
    }

    /**
     * @param array $array
     * @param $attr
     */
    private function setAnnotation(array $array, $attr){
        if($array[1] == '')
            $annotation = ucfirst(trim(preg_replace('/\s\s+/', '', $array[3])));
        else
            $annotation = ucfirst(trim(preg_replace('/\s\s+/', '', $array[1])));

        if(array_key_exists($annotation, $this->_attributes))
            $this->_annotations[$attr][$annotation] = trim($array[2] == '' ? 'true' : str_replace(";", "", $array[2]));
    }

    /**
     * @param array $array
     * @param $attr
     */
    private function setAnnotationMethod(array $array, $attr){
        if($array[1] == '')
            $annotation = ucfirst(trim(preg_replace('/\s\s+/', '', $array[3])));
        else
            $annotation = ucfirst(trim(preg_replace('/\s\s+/', '', $array[1])));

        $this->_annotationsMethods[$attr][$annotation] = trim($array[2] == '' ? 'true' : str_replace(";", "", $array[2]));
    }

    /**
     * @param: $class
     * @return: mixed
     * @throws: AnnotationException
     * @description: Fills the attribute class as the type of last variable and checks whether its use is possible
     */
    private function getClass($class){
        if(is_object($class))
            return $this->_class = &$class;

        if(class_exists($class))
            return $this->_class = new $class();

        throw new AnnotationException("AnnotationError: Esta classe não existe.", 1);
    }

    /**
     * @return array
     */
    public function getAnnotations(){
        return $this->_annotations;
    }

    /**
     * @return array
     */
    public function getAnnotationsMethods(){
        return $this->_annotationsMethods;
    }


    /**
     * @return array
     */
    public function getAttributes(){
        return $this->_attributes;
    }

    /**
     * @param $attr
     * @return null
     */
    public function getAnnotationsByAttribute($attr){
        $annotations = $this->getAnnotations();
        if(isset($annotations[$attr]))
            return $annotations[$attr];

        throw new AnnotationException("AnnotationError: field not found", 2);
    }

    /**
     * @param $attr
     * @return null
     */
    public function getAnnotationsByMethod($attr){
        $annotations = $this->getAnnotationsMethods();

        if(isset($annotations[$attr]))
            return $annotations[$attr];

        throw new AnnotationException("AnnotationError: field not found", 2);
    }

    public function getName($campo){
        if(array_key_exists("DisplayName", $this->_annotations[$campo]))
            return $this->_annotations[$campo]["DisplayName"];

        return $campo;
    }

}

