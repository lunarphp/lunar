# Staff Members

Your staff members are essentially users who can log in to the Admin Hub and have permissions assigned to them. Staff members are not to be confused with users in the `users` table, the Admin Hub uses a different table for authenticating users in the hub. This is a design choice to ensure that your customers can never accidentally be given access.

## Permissions

Permissions are assigned to staff and this dictates what they can do or see in the hub. If a user does not have a certain permission to view a page or perform an action they will get an Unauthorized HTTP error. They will also potentially see a reduced amount of menu items throughout the hub.

To enable permissions on a staff member, simply edit them via the settings page and assign the permissions you want them to have.

### Super Admins

By default the hub will have one super admin. You can assign more but non admins cannot assign other admins.
