# Open20 ElasticSearch #

Plugin description

### Installation ###

Add module to your main config in backend:
	
```php
<?php
'modules' => [
    'elasticsearch' => [
        'class' => '\open20\elasticsearch\Module',
        'modelMap' => [
            'ElasticModelSearch' => 'common\modules\transformermanagers\ElasticModelSearch',
        ],
        'hosts' => ['http://localhost:9201', ],
        "foldingClass" => 'open20\elasticsearch\models\folding\ItalianFolding',
        'modelsEnabled' => [
          	/**
             * Add here the classnames of the models where you want the elasticsearch => class trasformation of model
             * (i.e. 'open20\amos\news\models\News' => 'common\modules\transformermanagers\NewsTransformerManager' )
             */
        ],
        'indexes_setting' => [
		'italian' => [
			"analysis" => [
			  "filter" => [
				"italian_elision" => [
				  "type" => "elision",
				  "articles"=> [
						"c", "l", "all", "dall", "dell",
						"nell", "sull", "coll", "pell",
						"gl", "agl", "dagl", "degl", "negl",
						"sugl", "un", "m", "t", "s", "v", "d"
				  ],
				  "articles_case"=> true
				],
				"italian_stop"=> [
				  "type" => "stop",
				  "stopwords" => "_italian_" 
				],
				"italian_keywords" => [
				  "type" => "keyword_marker",
				  "keywords" =>["esempio"] 
				],
				"italian_stemmer" => [
				  "type" =>"stemmer",
				  "language" =>"light_italian"
				]
			  ],
			  "analyzer" => [
				"open20_italian" => [
				  "tokenizer" => "standard",
				  "char_filter" => [
					"html_strip"
				  ],
				  "filter" => [
					"_ascii_folding" => [
						"type" => "asciifolding",
						"preserve_original" => true
					],
					"italian_elision",
					"lowercase",
					"italian_stop",
					"italian_keywords",
					"italian_stemmer"
				  ]
				]
			  ]
			]
		]
	  ]
    ],
],
```

luya command 

```php
php vendor/bin/luya elastic/re-index-cms // rebuild cms pages elasticsearch index.

php vendor/bin/luya elastic/rebuild // rebuild all Record models elasticsearch index

php vendor/bin/luya elastic/clear-all-indexes // clear all elasticsearch index.

php vendor/bin/luya elastic/remove-all-indexes // remove all elasticsearch index configured in modelsEnabled.

php vendor/bin/luya elastic/create-all-indexes --index_settings_name=italian // create all elasticsearch index configured in modelsEnabled using index_settings_name = indexes_setting element.

php vendor/bin/luya elastic/set-settings --index_name=navitem --index_settings_name=italian //set index setting by  --index_name and using index_settings_name = indexes_setting element.

php vendor/bin/luya elastic/open-index --index_name=navitem // open index by --index_name.

php vendor/bin/luya elastic/close-index --index_name=navitem // close index by --index_name, must be used before elastic/set-settings.

php vendor/bin/luya elastic/remove-index --index_name=community // remove index by --index_name.

php vendor/bin/luya elastic/create-index --index_name=community --index_settings_name=italian // create index by --index_name using index_settings_name = indexes_setting element.

```

Parametri

```php
enableCwh (bool): a true attiva' la migrazione dei dati della cwh (Tags e pubblicazione) e la ricerca in base ai criteri own interest.

indexPrefixName (string): parametro obbligatorio in cui si indicano almeno 3 caratteri per il prefisso del nome degli indici creati. Il prefisso verra' utilizzato nelle query per limitare le ricerche solo agli indici prefissati.

```

