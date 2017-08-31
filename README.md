## pages and configurations

In vcms, a page is pretty similar to a directory, so for instance if you request <b>http://mywebsite.com/path/to/page</b>, the content and informations of that page are contained in <b>./pages/path/to/page</b> from your project root directory.

There are two types of configuration :

- page specific : you need to create a `resource.json` file in the page directory. The properties are only applied to the page you are editing
- inherited : you need to create a `inherit.json` file in a page directory. The page directory and all the descendants page directories from that location will inherit the properties of the `inherit.json` file.

### types of configuration



## database

In an `inherit.json` file or in a page `resource.json` config file, you can tell if the page needs to initialize a database connection

```
{
    ...,
    "needs-database": true,
    "database": "local",
    ...
}
```

the `"database"` attribute refers to the database handler used for the further sql transactions.

After you set up the page, it is really just a matter of using the EntityManager class to start calling some data.

```php
class Employee {}
$em = EntityManager::get('company_name.employees', 'Employee');
$john = $em->get_statement('select * where name=\'John Doe\'')->fetch();
echo "{$john->name} is {$john->age} years old";
```