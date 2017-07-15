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

To prepare the database, open `.credentials` file in the framework directory. and replace the example line with your values, for instance,

```
localhost:MyWebsiteDatabase:secr3tPa55word
```

If you have more than one database you can add new lines with the same password, the `.credentials` file keeps your database connection informations and later you can tell the framework which database to use for a specific script or website page.

That's all your database is ready.

*note: If you want to rename the `.credentials` file. You can edit the `Database.class.php` file and change the constant `CREDENTIALS_FILENAME` in the class definition.*



## Prepare your website

Now your framework is already ready to be used.
Before starting a project, it's important to think about the structure of your website.
Generally speaking, we develop a website in a sources directory (e.g. `src` or `sources`). As your website will get complex, the files in the `src` directory will get mixed and minimized and thrown in a distribution directory (e.g. `dist` or `build`). So let's create an `index.php` file in the following structure,

```
.
└── src
    └── www
        └── index.php
```

(*You should tell Apache to serve php files from `./src/www/`, wherever your project is. Making a VirtualHost for example.*)

In your `index.php` file, all you need to do is to call the **bootstrap** file of the framework.

```php
<?php

require_once('/usr/local/include/php/vcms/bootstrap.php');
```

the bootstrap file initializes the projects for you. It defines the autoloader, set some useful variables for the projects, and prepare the database if any.