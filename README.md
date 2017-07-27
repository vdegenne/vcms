<img src="img/hero.png"/>

# vcms

vcms is a personal php & apache CMS (the v is for my first name : valentin).


## installation

The first thing to install `vcms` is to get the content of this git. you can install using the git command :

```
git clone https://github.com/vdegenne/vcms.git
```


I recommend to place the downloaded framework in an appropriate place on your filesystem.
For instance, on a unix-based filesystem, consider placing the framework in `/usr/local/include/php/vcms`. But make sure you have access rights, because later on you'll need to create and edit files in this CMS structure.

Once you're done, the next step is to change the settings of the database if you are using one.
If you are not using any database for now, you can jump to the next chapter. Come back here any time 
you need to set up one.

### Prepare the database

To prepare the database, create a file named `.credentials` in the root of the framework directory. And add a line with the following syntax :

```
<db_handler>:<driver>:<database_ip>:<database_name>:<database_user>:<database_user_password>
```

-**<db_handler>**: a unique identifier to use in later in the framework to refer to the `dns`.\
-**\<driver\>**: one of the following, **pgsql**, **mysql**.\
-**<database_ip>**: the ip address of the database.\
-**<database_name>**: the name of the database to use.\
-**<database_user>**: the user to use for the connection.\
-**<database_user_password>**: the password of the previous username.\
 
for instance, the following line is valid.
```text
localdb:pgsql:192.168.204.152:my_database:postgres:12345
```

You can register as many endpoints as you want, just add a new line for each connection informations in the `.credentials` file. The `.credentials` keeps your database connection informations and later you can tell the framework which database to use for a specific script or website page.

That's all your database is ready.

*note: If you want to rename the `.credentials` file. You can edit the `Database.class.php` file and change the constant `CREDENTIALS_FILENAME` in the class definition.*



## Prepare your website

Now your framework is already ready to be used.
Before starting a project, it's important to think about the structure of your website.
Generally, we develop a website in a sources directory (e.g. `src` or `sources`).
As your website will get complex, the files in the `src` directory will get mixed and minimized and thrown in a distribution directory (e.g. `dist` or `build`).

The **[vcms-project-starter](https://github.com/vdegenne/vcms-project-starter)** provides a minimal structure for a `vcms` project. The project structure and files are important in `vcms`. Respecting this structure ensures that `vcms` will work properly and, one rock two birds, it'll give you a very consistent starting point for building all kind of projects, from websites to enterprise object, because the file-structure were optimized and is not just vcms-wise.

However, I will in the next of this tutorial creates the directories and files for a new `vcms` project, so you can really understand how the framework works.

So let's start with the following structure.

```
.
└── src
    ├── config.json
    ├── includes
    ├── pages
    └── www
        └── index.php
```

-**note1**:*You should tell Apache to serve php files from `./src/www/`, wherever your project is. Making a VirtualHost for example.*\
-**note2**:*the `mod_rewrite` apache module MUST be installed in order for the framework to work.*\
-**note3**:*everything in the `www` directory will be public, so every sensitive informations should be out of that location.*

### project configuration (config.json) 

Before bootstrapping and creating the pages, we need to edit the `config.json`. This file gives some project informations to the framework that can't otherwise be deducted by the bootstrap process.

`config.json` is a basic json file and here are the possible values.

```json
{
  "project_name": "<your_project_name>",
  
}
```

### bootstraping

In your `index.php` file, all you need to do is to call the **bootstrap** file of the framework.

```php
<?php
require_once '/usr/local/includes/php/vcms/bootstrap.php';
```

The bootstrap file initializes the projects for you. It defines the autoloader, set some useful variables for the projects, and prepare the database if any.

## the pages

Before creating a page to demonstrate how the framework manages them, we have to understand how they are organized. A page location is matching the exact same path of the http request uri. For instance if a user requests `http://example.com/welcome`, the path to the page is `./pages/welcome/.`. This way we always know where the page remains and can locate it quickly.

(note: you can change this behavior either in`.htaccess` or 