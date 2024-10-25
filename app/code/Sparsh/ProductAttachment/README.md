##Product Attachment Extension

This extension allows you to upload files of chosen format by providing users with accurate manuals, licenses, and extra info right on product pages.

##Support:
version - 2.3.x, 2.4.x

##How to install Extension

1. Download the archive file.
2. Unzip the file
3. Create a folder [Magento_Root]/app/code/Sparsh/ProductAttachment
4. Drop/move the unzipped files to directory '[Magento_Root]/app/code/Sparsh/ProductAttachment'

#Enable Extension:
- php bin/magento module:enable Sparsh_ProductAttachment
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush

#Disable Extension:
- php bin/magento module:disable Sparsh_ProductAttachment
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush
