## Social Media Technical Documents

### Local development run

```
# start union
cd union
php artisan serve --port=8000
# OR
php artisan serve --host=localhost --port=8000

# init union - create admin user
php artisan union:init

```

```
# start video
cd video/backend
php artisan serve --port=8001
# OR
php artisan serve --host=localhost --port=8001

cd video/frontend
npm run serve

# init video - create admin user
php artisan video:init

# reset menu and permission - create menus
php artisan video:reset-menu-permission
```

```
# start bank

cd bank/bankend
php artisan serve --port=8002
# OR
php artisan serve --host=localhost --port=8002

cd bank/frontend
npm run serve

# init bank - create admin user and settings
php artisan bank:init

# reset menu and permission - create menus
php artisan bank:reset-menu-permission
```

```
# start vpn
php artisan serve --port=8003
# OR
php artisan serve --host=localhost --port=8003

cd vpn/frontend
npm run serve

# init vpn - create admin user and settings
php artisan vpn:init

# reset menu and permission - create menus
php artisan vpn:reset-menu-permission

```
