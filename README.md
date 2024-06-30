# Shopify Payment Customization App

## Requirement

- PHP >= 8.2
- Node >= 18
- npm >= 10

### Ngrok (Only for local developing)

Download from https://ngrok.com/

### Setup project

- Laravel setup

```sh
    > cp .env.example .env (Set the Database credentials)
    > composer install
    > php artisan key:generate
    > php artisan migrate
    > php artisan db:seed
    > php artisan queue:work
    > php artisan schedule:work
```

- Compile VueJS assets

```sh
    > npm install
    > npm run dev
```

- Install Shopify Cli

```sh
    > npm install -g @shopify/cli
```

- Up the Laravel project

```sh
    php artisan serve
```

- Start the ngrok tunnel

```sh
    ngrok http 8000
```

### Create App in your store and set key in `.env` file

`APP_URL` = "ngrok url or live website url"
`SHOPIFY_API_KEY`= ""  
`SHOPIFY_API_SECRET`= ""  
`NAMESPACE`="payment-customization"  
`KEY`="function-configuration"  
`APP_ENV` = "production" (only on live instance default value is local)  
`APP_NAME` = "set app name for email"

### Set your configuration on`shopify.app.toml` file

`client_id` = "SHOPIFY_API_KEY"  
`name` = "copy from your app configuration menu"  
`handle` = "copy from your store configuration menu"  
`application_url` = "https://striking-mildly-catfish.ngrok-free.app/"  
`dev_store_url` = 'set store name'  
`redirect_urls` = [ "https://striking-mildly-catfish.ngrok-free.app/authenticate" ]  

[webhooks.privacy_compliance]  
<!-- `customer_deletion_url` = "https://paymentapp.patoliyainfotech.com/api/webhooks/customer_deletion"  
`customer_data_request_url` = "https://paymentapp.patoliyainfotech.com/api/webhooks/customer_request"  
`shop_deletion_url` = "https://paymentapp.patoliyainfotech.com/api/webhooks/deletion"   -->

Set ngrok URL in your store App configuration menu

### Compile payment customization extension

```sh
    > cd payment-app
    > npm run shopify app deploy Or shopify app deploy
    > npm run dev
```

### If any change in `run.graphql` FILE then run cmd

goto directory

```sh
    > cd payment-app/extensions/hide-customization or rename-customization or sort-customization
    > cd npm install
    > npm run shopify app function typegen
    > shopify app build
```

### Set FUNCTION_ID in `.env` file

--> go to your store app extension menu and click on extension name and copy ID and paste it

`HIDE_FUNCTION_ID`=
`RENAME_FUNCTION_ID`=
`SORT_FUNCTION_ID`=

### Check Payment Customization create or not `PATH` in your Store Settings

- Goto store `settings` then `payments` menu and check `Payment method customizations` heading

### Slack support notification webhook URL

SLACK_NOTIFICATION_URL=""

### Set Mail configuration on env

`MAIL_MAILER`=smtp  
`MAIL_HOST`="set host"  
`MAIL_PORT`=port  
`MAIL_USERNAME`=username  
`MAIL_PASSWORD`=password  
`MAIL_ENCRYPTION`=ssl  
`MAIL_FROM_ADDRESS`= "set support mail address"  
`MAIL_FROM_NAME`="Checkout"  

### LIMIT (Just for Knowledge )

- App activate only 5 Payment customization (due to shopify limit)

### Debug Payment Function `PATH` (only for developing and testing purpose)

- Goto app and then extension menu and click extension name  



## Vendor Change Only On Production
-/var/www/html/shopify-custome-checkout/vendor/kyon147/laravel-shopify/src/Http/Middleware/VerifyShopify.php

-Function :- `installRedirect`

```sh
$url = 'https://paymentapp.patoliyainfotech.com/authenticate?'.http_build_query(['shop' => $shopDomain->toNative(), 'host' => request('host')]);
return redirect($url);
```


-/var/www/html/shopify-custome-checkout/vendor/kyon147/laravel-shopify/src/Traits/AuthController.php

-Function :- `authenticate` (in else near Goto home route)

```sh
$url = 'https://paymentapp.patoliyainfotech.com/?'.http_build_query(['shop' => $shopDomain->toNative(), 'host' => $request->get('host')]);
return redirect($url);
```