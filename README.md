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

and rename it `config.php`.

### How to use
Use the `generate.php` file to insert data into your database. The GitHub API will only let you get 
1000 issues max per call so you might want to use the `START_DATE` and `END_DATE` environment variables
to make sure you don't get too much issues at once.

Example:

```
START_DATE=2019-01-01 END_DATE=2019-12-31 php generate.php
```

By default, `START_DATE` is set to `2018-01-01` and `END_DATE` to the current date.

Then use the `index.php` file to browse the data.

### Update data

If, when inserting new issues, you insert new labels, you may want to link them to the existing `types` by setting up the 
`type_id` field in the `label` table. It's also possible to create new `types`, the form will 
be updated accordingly.

### Notes
Yes, I know it's very ugly but I don't really have the time to make it pretty. One day, maybe...
