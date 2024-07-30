# Staff Members

Your staff members are essentially users who can log in to the admin panel and have permissions assigned to them. Staff 
members are not to be confused with users in the `users` table, the Lunar uses a different table for authenticating 
users in the admin panel. This is a design choice to ensure that your customers can never accidentally be given access.

# Roles and permissions

In Lunar panel, we are utilizing roles and permission for authorization. This give you the ability to assign multiple 
permissions to a role and assign it to the staff without assigning permission one by one to the staff. 

::: tip
The admin panel is using `spatie/laravel-permission` package
:::

### Roles
Out of the box Lunar provided `admin` and `staff` roles. You can create new role using our Access Control page in Staff 
menu.
After installation, the panel will have one admin. You can assign more but non admins cannot assign other admins.

### Permissions
Permissions can be assigned to roles or directly to staff and this dictates what they can do or see in the panel. If a 
user does not have a certain permission to view a page or perform an action they will get an Unauthorized HTTP error. 
They will also potentially see a reduced amount of menu items throughout the admin panel.

To enable permissions on a staff member, simply edit them via the staff page and assign the permissions you want them 
to have.

##### Adding permissions
While the panel provided a page to create role and assign permissions. It's deliberated that permission are not created 
from the panel as the authorization are required to be implemented in code. It might change in the future but the 
recommended way to create roles and permission would be Lunar migration state or Laravel's migration. So you can deploy 
it to other environment easily.

## Authorisation
First party permission provided by Lunar are used to authorise repective section of the panel. You should still 
implement authorisation checking respectively for your new permissions and custom pages.

Example:
`middleware` 'can:permission-handle'
`in-code` Auth::user()->can('permission-handle')
:::
