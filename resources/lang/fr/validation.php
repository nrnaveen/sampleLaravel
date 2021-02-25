<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Validation Language Lines
	|--------------------------------------------------------------------------
	|
	| The following language lines contain the default error messages used by
	| the validator class. Some of these rules have multiple versions such
	| as the size rules. Feel free to tweak each of these messages here.
	|
	*/

	'accepted' => ":attribute doit être accepté.",
	'active_url' => ":attribute n'est pas un URL valide.",
	'after' => ":attribute doit être une date après :date.",
	'alpha' => ":attribute ne peut contenir que des lettres.",
	'alpha_dash' => ':attribute ne peut contenir que des lettres, des nombres et des tirets.',
	'alpha_num' => ':attribute ne peut contenir que des lettres et des nombres.',
	'array' => ':attribute doit être un tableau.',
	'before' => ':attribute doit être une date avant :date.',
	'between' => [
		'numeric' => ':attribute doit être entre :min et :max.',
		'file' => ':attribute doit être entre :min et :max kilo-octets.',
		'string' => ':attribute doit être entre :min et :max caractères.',
		'array' => ':attribute doit avoir entre :min et :max éléments.',
	],
	'boolean' => 'Le champ :attribute doit être vrai ou faux.',
	'confirmed' => 'La confirmation :attribute ne correspond pas.',
	'date' => ":attribute n'est pas une date valide.",
	'date_format' => ':attribute ne correspond pas au format :format.',
	'different' => ':attribute et:other doivent être différents.',
	'digits' => ':attribute doit être :digits chiffres.',
	'digits_between' => ':attribute doit être entre :min et :max chiffres.',
	'distinct' => 'Le champ :attribute field a une valeur en double.',
	'email' => ':attribute doit être une adresse mail valide.',
	'exists' => ':attribute sélectionné est invalide.',
	'filled' => 'Le champ :attribute est obligatoire.',
	'image' => ':attribute doit être une image.',
	'in' => 'attribute sélectionné est invalide.',
	'in_array' => "Le champ :attribute n'existe pas dans :other.",
	'integer' => ':attribute doit être un entier.',
	'ip' => ':attribute doit être une adresse IP valide.',
	'json' => ':attribute doit être une chaîne de caractères JSON valide.',
	'max' => [
		'numeric' => ':attribute ne doit pas dépassé :max.',
		'file' => ':attribute ne doit pas dépassé :max kilo-octets.',
		'string' => ':attribute ne doit pas dépassé :max caractères.',
		'array' => ':attribute ne doit pas avoir plus de :max éléments.',
	],
	'mimes' => ':attribute doit être un fichier de type : :values.',
	'min' => [
		'numeric' => ':attribute doit être au minimum :min.',
		'file' => ':attribute doit être au minimum :min kilo-octets.',
		'string' => 'The :attribute doit être au minimum :min caractères.',
		'array' => 'The :attribute doit avoir au minimum :min éléments.',
	],
	'not_in' => ':attribute sélectionné est invalide.',
	'numeric' => ':attribute doit être un nombre.',
	'present' => 'Le champ :attribute doit être présent.',
	'regex' => 'Le format:attribute est invalide.',
	'required' => 'Le champ :attribute field est obligatoire.',
	'required_if' => 'Le champ :attribute est obligatoire quand :other est :value.',
	'required_unless' => "Le champ :attribute field is required unlessn'est obligatoire que si :other est dans :values.",
	'required_with' => 'Le champ :attribute est obligatoire quand :values est présent.',
	'required_with_all' => 'Le champ :attribute est obligatoire quand :values est présent.',
	'required_without' => 'Le champ :attribute est obligatoire quand :values est absent.',
	'required_without_all' => "Le champ :attribute est obligatoire quand aucun des :values n'est présent.",
	'same' => ':attribute et :other doivent correspondre.',
	'size' => [
		'numeric' => ':attribute doit être :size.',
		'file' => ':attribute doit être :size kilo-octets.',
		'string' => ':attribute doit être :size caractères.',
		'array' => ':attribute doit contenir :size éléments.',
	],
	'string' => ':attribute doit être une chaîne de caractères.',
	'timezone' => ':attribute doit être une zone valide.',
	'unique' => ':attribute a déjà été pris.',
	'url' => 'Le format :attribute est invalide.',

	/*
	|--------------------------------------------------------------------------
	| Custom Validation Language Lines
	|--------------------------------------------------------------------------
	|
	| Here you may specify custom validation messages for attributes using the
	| convention "attribute.rule" to name the lines. This makes it quick to
	| specify a specific custom language line for a given attribute rule.
	|
	*/

	'custom' => [
		'attribute-name' => [
			'rule-name' => 'custom-message',
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Custom Validation Attributes
	|--------------------------------------------------------------------------
	|
	| The following language lines are used to swap attribute place-holders
	| with something more reader friendly such as E-Mail Address instead
	| of "email". This simply helps us make messages a little cleaner.
	|
	*/

	'attributes' => [],

];