<?php

class Person
{
	public $name;
	function __construct($name) 
	{
		$this->name = $name;
	}
}

interface Module 
{
	function execute();
}

class FtpModule implements Module
{
	function setHost($host) 
	{
		print "FtpModule::setHost(): $host <br>";
	}

	function setUser($user) 
	{
		print "FtpModule::setUser(): $user <br>";
	}

	function execute()
	{
		//implementar codigo aqui.
	}

}

class PersonModule implements Module
{
	function setPerson(Person $person) 
	{
		print "PersonModule::setPerson(): {$person->name} <br>";
	}

	function execute()
	{
		//implementar codigo aqui.
	}
}

class ModuleRunner
{
	private $configData = array(
				'PersonModule' => array(
					'person' => 'bob'
				),
				'FtpModule' => array(
					'host' => 'exemple.com',
					'user' => 'anon'
				)
			);

	private $modules = array();

	function init()
	{
		$interface = new ReflectionClass('Module');
		foreach($this->configData as $modulename => $params) {
			$module_class = new ReflectionClass($modulename);
			if(!$module_class->isSubclassOf($interface)) {
				throw new Exception("desconhecido tipo de módulo: $modulename");
			}
			$module = $module_class->newInstance();
			foreach($module_class->getMethods() as $method) {
				$this->handleMethod($module, $method, $params);
			}
			array_push($this->modules, $module);
		}
		print_r($this->modules[0]);
	}

	function handleMethod(Module $module, ReflectionMethod $method, $params)
	{
		$name = $method->getName();
		$args = $method->getParameters();

		if(count($args) != 1 || substr($name, 0, 3) != 'set') {
			return false;
		}

		$property = strtolower(substr($name, 3));
		if(!isset($params[$property])) {
			return false;
		}

		$arg_class = $args[0]->getClass();
		if(empty($arg_class)) {
			$method->invoke($module, $params[$property]);
		} else {
			$method->invoke($module, $arg_class->newInstance($params[$property]));
		}
	}
}

$teste = new ModuleRunner();
$teste->init();