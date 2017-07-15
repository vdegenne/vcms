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

**note: If you want to rename the `.credentials` file. You can edit the `Database.class.php` file and change the constant `CREDENTIALS_FILENAME` in the class definition.**



## Prepare your website

Now your framework is already ready to be used.