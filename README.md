# DiZed Magento 2 Wishlist Guest Module

## Additional wishlist functionalities for the Magento 2 project.

A module that will allow unauthorized users to use the wishlist on your website. The ability to share a wishlist via a URL has also been added.

###### Developed and tested on Magento 2.4 version and PHP 8.1 version. But it should be compatible with earlier versions.

##### Key Features:

- Working with a wishlist for guest customers.
- Sharing wishlist by URL.
- Automatic removal of old wishlists from the database.

### Installation.

```code
composer require dized/module-wishlist-guest

bin/magento setup:upgrade --keep-generated
bin/magento setup:di:compile
bin/magento cache:clean
```

**IMPORTANT** to enable and configure the module in Magento Admin: **Admin Panel -> Stores -> Settings -> Configuration -> DiZed Team Extensions -> Wishlist Guest**.

![Module Configuration](https://raw.githubusercontent.com/di-zed/internal-storage/main/readme/images/m2-wishlist-guest/config_wishlist_guest.png)

### Add a link to the website with the URL to the wishlist for the guest customers.

If your site doesn't have a link to a wishlist for unauthorized customers, you should add one (**{project-domain}/wishlist**).
After that, by clicking on the link, the customer will be able to get to the wishlist, as well as share it with other customers just using the link.

![Module Preview](https://raw.githubusercontent.com/di-zed/internal-storage/main/readme/images/m2-wishlist-guest/share_link_preview.png)

### Additional useful features.

- For independent work with wishlist functionalities in your own module: **\DiZed\WishlistGuest\Api\WishlistManagementInterface**.
