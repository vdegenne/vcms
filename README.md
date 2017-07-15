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

To prepare the database, you need to create a file called `.credentials` on the root of the framework directory.