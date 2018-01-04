# php-alexa-skill
Alexa Skill PHP Wrapper

## Features

* Crytographic validation of request origin (Amazon)
* Session handling

## Usage

``` php
<?php
require("AlexaSkill.php");

// create a skill
$skill = new AlexaSkill();

// add a simple intent
$skill->intent("SayHello", function($request, &$session) {
	return [
		"outputSpeech" => [
			"type" => "SSML",
			"ssml" => "<speak>Hello, World!</speak>"
		],
		"shouldEndSession" => true
	];
});

$skill->run();
```
