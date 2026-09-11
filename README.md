# CITE POS

CITE POS is a PHP and MySQL point-of-sale system for the IT Department. It supports sales, inventory and batch management, student releases, transactions, user management, and audit logs. The interface is built with Tailwind CSS.

## 🛠 Prerequisites

- XAMPP (Apache, PHP 8.0+ and MySQL/MariaDB)
- Node.js and npm (to rebuild the Tailwind stylesheet)
- PHP PDO MySQL extension enabled

## 🚀 Installation & Setup

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

## 🗄️ Database Management
To set up your database tables and default data, you will need to run the provided PHP migration and seeder scripts via your Bash terminal.

```⚠️ IMPORTANT: Verify that your terminal is pointing to the exact location where your project is uploaded inside the XAMPP htdocs folder.```

Step 1: Run Migrations

Open your bash terminal.

Navigate to the migrations directory:

```Bash
cd D:\xampp\htdocs\cite-pos\database\migrations
```
Execute the migration script:

```Bash
php Migrate.php```

Step 2: Run Seeders

If you are continuing in the same terminal window immediately after running the migration:

Navigate back one directory and into the seeders folder:

```Bash
cd ..
cd seeders```

Execute the seeder script:

```Bash
php UserSeeder.php```

If you are opening a new terminal window:

Navigate directly to the seeders directory:

```Bash
cd D:\xampp\htdocs\cite-pos\database\seeders```

Execute the seeder script:

```Bash
php UserSeeder.php```


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

## 📝 Development Notes

- `public/index.php` is the application router and should be the web server entry point.
- Keep secrets in `.env`; `.gitignore` excludes local environment files.
- Re-run `npm run build:css` when changing Tailwind classes in PHP views.
