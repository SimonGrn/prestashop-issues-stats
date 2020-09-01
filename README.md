# prestashop-issues-stats

This is a small tool which gather all issues with the `Bug` label of the 
[PrestaShop project](https://github.com/PrestaShop/PrestaShop).
It then display them in a pie chart and let you choose which ones you want to see.

### How to install
Use `composer install` to install all the dependencies.

Create a database using the `schema.sql` file at the root of the project.

Copy the `config.php.dist` file:
* change the values inside for your correct MySQL values
* add your [Github token](https://github.com/settings/tokens/new)
* add a security token (any passphrase will do)

and rename it `config.php`.

### How to use
Use the `generate.php` file to insert data into your database. 

:warning: you must declare an environment variable `SECURITY_TOKEN` identical to the one you set up above. 


```
SECURITY_TOKEN=my_token php generate.php
```

The script will gather **all** the issues in the repository and either insert them or update them if they were closed since
its last passage.

Then use the `index.php` file to browse the data.

### Update data

If, when inserting new issues, you insert new labels, you may want to link them to the existing `types` by setting up the 
`type_id` field in the `label` table (manually !). It's also possible to create new `types`, the form will 
be updated accordingly.

### Notes
Yes, I know it's very ugly but I don't really have the time to make it pretty. One day, maybe...
