# Lynx Distribution DMCC - TLM International

[https://bizaar.stgin.com/](https://bizaar.stgin.com/)

## Requirements
* PHP 8.1
* NodeJS 16
* Mysql 8.0
* Elasticsearch

## Installation

1. Run `bash composer install`
2. copy env file from staging
3. Run `bash bin/magento setup:upgrade`
4. Run `bash bin/magento cache:flush`

## CSS Compilation
1. To run watcher, run `npm run watch`
2. To run production build, run `npm run build`


## Hints

If receiving a message:
~~~
Exception #0 (Magento\Framework\Exception\LocalizedException): Unable to find a physical ancestor for a theme 'Bizaar Hyva theme'.
~~~
Go into `theme` table and change `type` to `0` for all themes.


### ElasticSuite configuration
You have to do this if your search is not working.
If you haven't set ElasticSuite as your Search Engine yet, it should be added with configurator or under Catalog -> Catalog -> Catalog Search -> Search Engine

Also make sure you have installed necessary elasticsearch plugins
```shell
cd /usr/share/elasticsearch
bin/elasticsearch-plugin install analysis-phonetic
bin/elasticsearch-plugin install analysis-icu
```
Do full reindex to update elasticsearch indices.
