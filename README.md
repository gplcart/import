[![Build Status](https://scrutinizer-ci.com/g/gplcart/import/badges/build.png?b=master)](https://scrutinizer-ci.com/g/gplcart/import/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gplcart/import/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gplcart/import/?branch=master)

Importer is a powerful tool for [GPL Cart](https://github.com/gplcart/gplcart) powered sites that intended for bulk product creation/updating using CSV files as a data source.

**Features**

- Add/update products
- Can handle *thousands* of products on a cheap hosting without any server configuration
- Can download remote images
- Simple settings and UI


**Installation**

1. Download and extract to `system/modules` manually or using composer `composer require gplcart/import`. IMPORTANT: If you downloaded the module manually, be sure that the name of extracted module folder doesn't contain a branch/version suffix, e.g `-master`. Rename if needed.
2. Go to `admin/module/list` end enable the module
3. Adjust settings at `admin/module/settings/import`
4. Allow administrators to use Importer by giving them permissions `Importer: import products` at `admin/user/role`. Also make sure that administrators have permissions to create/update products and upload files

**Usage**

- Go to `admin/tool/import` and import products