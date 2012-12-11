<?php
require_once('extractors/mysql/MysqlModelExtractor.php');
require_once('generators/php/PhpGenerator.php');


const db_dsn = 'mysql:host=localhost;dbname=wmi;user=root;pass=test';

$opts = getopt('', array('host::', 'db:', 'user::', 'pass:', 'ns::', 'dst::'));

if(!$opts)
	die('Please enter all required options: --db, --pass');

$defaults = array('host' => 'localhost',
                  'user' => 'root',
		          'ns'   => 'gen\\Models',
		          'dst'  => 'gen');

$opts = array_merge($defaults, $opts);

printf("Connecting to '%s' with username '%s' using database '%s' to generate files in '%s' with namespace '%s'\n", $opts['host'], $opts['user'], $opts['db'], $opts['dst'], $opts['ns']);
$dsn = sprintf('mysql:host=%s;dbname=%s', $opts['host'], $opts['db']);
$PDO = new PDO($dsn, $opts['user'], $opts['pass']);

/* 
 * MySQL ===========\                                                            /=== PHP
 * PostreSQL --------\ IModelExtractor   Unified Model Interface    IGenerator  /---- Javascript
 * JSON Description -----> Extract ------------> Model ------------> Generate ------- SQL Script
 * UML --------------/                                                          \---- C#
 *                                                                               \--- Java etc
 *
 * = Already completed path
 * - Hypotethical path
 */

$extractor = new MysqlModelExtractor($PDO);
foreach($extractor->getModels() as $model)
{
	$gen = new PhpGenerator($model, 'generators/php/templates/model/', $opts['ns']);
	$gen->generate();
	$gen->save($opts['dst']);
}

echo "Success!\n";

?>
