<?php
use PhpInjector\Injector;
	require_once(__DIR__.'/vendor/autoload.php');

	class MyClass {
		/**
		 * Some text
		 *
		 * @param string $b Some string
		 * @param int $a A number
		 */
		public function methodA($a, $b, $clex = 'weather', $d_and_some = 1, $test = null) {
			echo "Hello {$a}, how is {$b}, and what about {$clex}?\n";
		}
	}

	function myFunc($a, $b, array $c = null, $d = "alex"){}

	$my = new MyClass();
	$injectorMethod = new Injector(array($my,'methodA'), ['allow_unknown_params' => false]);
	//$injectorFunction = new Injector('\PhpInjector\myFunc');
	/*$injectorClosure = new Injector(function($a,int $b,bool $c = null){

	});*/

	// var_dump($injectorMethod);
	// var_dump($injectorFunction);

	$injectorMethod->invoke(array('b' => 5,'a'=>'hello','test'=>'yes'));
	// $injectorFunction->invoke(array('b' => 5,'a'=>'hello'));

	$inj = new Injector('strtolower');
	var_dump($inj->getInputParameters());
	var_dump($inj->invoke(array('str'=> 'I SHOULD BE BIG!')));
