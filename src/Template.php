<?php

namespace phyrex;

use \Exception;

class Template {
	protected $tpl;
	protected $vars = [];
	public $arrays = [];
    static public $filters = [];
    static private $hooks = [];
    
    public function __construct(string $tpl, array $vars = []) {
		if(is_file($tpl) && !file_exists($tpl)) throw new Exception("Bad Parameter \$tpl");
        $this->tpl = (is_file($tpl)) ? file_get_contents($tpl) : $tpl;
		$this->vars = $vars;
    }
    
    protected function process() {
        $callback = function($matches) {
            array_shift($matches);
            $filter = $matches[0];
            $mods = substr($matches[1], 2);
            ob_start();
            if(isset(self::$filters[$filter])) {
                $filter = self::$filters[$filter];
                $filter->call($this, ...explode("::", $mods));
                return trim(ob_get_clean());
            }
        };
        
        $this->tpl = preg_replace_callback("/<{([a-z]+)(::((?!}>).)*)?}>/is", $callback->bindTo($this), $this->tpl);
        if(preg_match("/<{([a-z]+)(::((?!}>).)*)?}>/is", $this->tpl)) $this->process(false);
    }
	
	public function __set($key, $val) {
		$this->vars[$key] = $val;
	}
	
	public function __toString() {
		$this->process();
		return $this->tpl;
    }

    public function __call($name, $args) {
        if(isset(self::$hooks[$name]) && is_callable(self::$hooks[$name])) self::$hooks[$name]->call($this, ...$args);
    }
    
    static function addFilter(string $name, callable $handler) {
        self::$filters[$name] = $handler;
    }

    static function addHook(string $name, callable $handler) {
        self::$hooks[$name] = $handler;
    }
}


Template::addFilter("include", function($file) { 
    if(file_exists("{$file}.php")) include "{$file}.php"; 
});

Template::addFilter("var", function($var) {
    if(array_key_exists($var, $this->vars)) echo $this->vars[$var];
});

Template::addFilter("array", function($key, $tpl) {
    if(array_key_exists($key, $this->arrays) && is_array($this->arrays[$key])) {
        foreach($this->arrays[$key] as $i) {
            if(is_array($i)) {
                $tplc = $tpl;
                foreach($i as $k => $v) $tplc = str_replace("@{$k}", $v, $tplc);
                echo $tplc;
            }
        }
    }
});

Template::addHook("push", function($array, $value) {
    if(!array_key_exists($array, $this->arrays)) $this->arrays[$array] = [];
    $this->arrays[$array][] = $value;
});