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
        'modelsEnabled' => [
          	/**
             * Add here the classnames of the models where you want the elasticsearch => class trasformation of model
             * (i.e. 'open20\amos\news\models\News' => 'common\modules\transformermanagers\NewsTransformerManager' )
             */
        ],
    ],
],
```

luya command 

```php
php vendor/bin/luya elastic/re-index-cms // rebuild cms pages elasticsearch index

php vendor/bin/luya elastic/rebuild // rebuild all Record models elasticsearch index

php vendor/bin/luya elastic/clear-all-indexes // clear all elasticsearch index 

```
