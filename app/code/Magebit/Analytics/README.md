# Description
Goal of the module is to provide all necessary marketing/analytics for SEO and Marketing

## Installation guide
General magento installation flow

### OptionA:
- Using composer and repo as source
- Add in your composer.json
```
"repositories": [
   "magebit-analytics": {
       "type":"package",
       "package": {
           "name": "magebit/analytics-module",
           "version":"master",
           "source": {
             "url": "https://github.com/magebitcom/analytics-module",
             "type": "git",
             "reference":"master"
           }
       }
   }
],
```
- Install by using following command
```
composer require magebit/analytics-module:master
```

### Option B:   
- Using git clone and Magento\Analytics method
```
mkdir {Magento_root}/app/code/Magebit
cd {Magento_root}/app/code/Magebit
git clone git@github.com:magebitcom/analytics-module.git Analytics
```

## Feature list
* GA4 features:
  * General configuration
  * Events:
    * Page view
    * Item view
    * Purchase
    * Scroll
    * From_star
    * User_engagement
    * Add to cart
* Google Adwords


## Support
*  PHP 7.4+
*  Magento 2.4.x
*  Hyva 1.1.x


## Notes
https://support.google.com/analytics/answer/9267735?hl=en
