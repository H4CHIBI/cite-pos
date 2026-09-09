# CITE POS

CITE POS is a PHP and MySQL point-of-sale system for the IT Department. It supports sales, inventory and batch management, student releases, transactions, user management, and audit logs. The interface is built with Tailwind CSS.

## Requirements

- XAMPP (Apache, PHP 8.0+ and MySQL/MariaDB)
- Node.js and npm (to rebuild the Tailwind stylesheet)
- PHP PDO MySQL extension enabled

## Setup

1. Copy the project into the XAMPP web root, for example `C:\xampp\htdocs\pos-cite`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Create a MySQL database named `pos_cite`.
4. Create a local `.env` file in the project root. Do not commit it:

   ```env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pos_cite
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Use the port and password configured by your local MySQL installation.

5. Run the database migrations. See [Running the migrations](#running-the-migrations) below.
6. Run `database/seeders/users.sql` to create the default development accounts.
7. Install frontend dependencies and build the stylesheet:

   ```bash
   npm install
   npm run build:css
   ```

   The current npm script watches for changes. Stop it with `Ctrl+C` after the stylesheet has been generated, or leave it running while developing.

8. Open [http://localhost/pos-cite/public/](http://localhost/pos-cite/public/) in a browser. If Apache rewrite rules are enabled, [http://localhost/pos-cite/](http://localhost/pos-cite/) also works.

## Running the migrations

The migration files must be executed in filename order because later tables reference earlier tables.

### Option 1: phpMyAdmin

1. Open `http://localhost/phpmyadmin`.
2. Create and select the `pos_cite` database.
3. Open the **Import** tab.
4. Select each file from `database/migrations/tables/`, starting with `001_create_departments.sql` and ending with `011_update_transaction_item_statuses.sql`.
5. Click **Import** for each file in order.
6. Import `database/seeders/users.sql` last to create the default accounts.

### Option 2: MySQL command line

From the project root, run the following command in PowerShell. Replace the host, port, username, and password with the values in your `.env` file:

```powershell
$mysql = 'C:\xampp\mysql\bin\mysql.exe'
$connection = @('-h', '127.0.0.1', '-P', '3306', '-u', 'root', 'pos_cite')

Get-ChildItem '.\database\migrations\tables\*.sql' |
    Sort-Object Name |
    ForEach-Object {
        Get-Content $_.FullName -Raw | & $mysql @connection
        if ($LASTEXITCODE -ne 0) {
            throw "Migration failed: $($_.Name)"
        }
    }

Get-Content '.\database\seeders\users.sql' -Raw | & $mysql @connection
if ($LASTEXITCODE -ne 0) {
    throw 'User seeder failed.'
}
```

If MySQL has a password, add `-p` to `$connection`; the client will prompt for it securely:

```powershell
$connection = @('-h', '127.0.0.1', '-P', '3306', '-u', 'root', '-p', 'pos_cite')
```

### Running one migration file

To run a specific migration instead of all migrations, use the same MySQL connection and pipe the file into the MySQL client:

```powershell
$mysql = 'C:\xampp\mysql\bin\mysql.exe'
$connection = @('-h', '127.0.0.1', '-P', '3306', '-u', 'root', 'pos_cite')
Get-Content '.\database\migrations\tables\001_create_departments.sql' -Raw | & $mysql @connection
```

Replace `001_create_departments.sql` with the migration file you want to execute. Run prerequisite migrations first when the selected file has foreign-key dependencies.

## Default accounts

The seeder creates these development accounts. All use the password `password`.

| Username | Role | Purpose |
| --- | --- | --- |
| `admin` | Administrator | Full system and user-management access |
| `cashier` | Cashier | Point of sale and transaction access |
| `officer` | Officer | Releasing and released-item access |

Change these passwords immediately in a non-development environment. The passwords are included in the seeder only for local setup.

## Project structure

```text
app/
  config/       Environment and database configuration
  controllers/  Request and business logic
  views/        PHP pages, partials, and modals
database/
  migrations/   Ordered table migrations
  seeders/      Initial development data
public/         Web entry point and published assets
src/css/        Tailwind CSS input
```

## Development notes

- `public/index.php` is the application router and should be the web server entry point.
- Keep secrets in `.env`; `.gitignore` excludes local environment files.
- Re-run `npm run build:css` when changing Tailwind classes in PHP views.
