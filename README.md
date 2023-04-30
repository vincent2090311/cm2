# Campaign Monitor connector for Magento 2

The connector between Magento2 and CampaignMonitor (https://www.campaignmonitor.com/)

## Install

1. Via Github
    + Clone code from repository
    + Copy content in folder src to your_installation_folder/app/code/Fuutur/Campaignmonitor
    + Run these commands 

    ```
    $ php bin/magento setup:upgrade 
    $ php bin/magento setup:static-content:deploy
    $ php bin/magento cache:clean
    ```
2. Via composer
    + Run command : composer require fuutur/module-campaignmonitor
    + Deploy content

    ```
    $ php bin/magento setup:upgrade 
    $ php bin/magento setup:static-content:deploy
    $ php bin/magento cache:clean
    ```

## Usage

* Register a Campaign Monitor account to get API key and ClientID
* Add your API key and ClientID to admin > Stores > Configuration > Campaign Monitor > General > API

## Note
I haven't implemented all API from Campaign Monitor to this module. All requests are welcome to improve this module.
