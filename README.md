# ACME Donations Platform

A modern donation platform built with Laravel 12, Vue.js 3, and Tailwind CSS using Inertia.js for seamless SPA functionality.

## 🚀 Quick Start (Docker-first) - Recommended

The easiest way to run the project is via Docker. This will build the PHP app, compile the frontend assets, and start everything for you.

```bash
chmod +x docker-rebuild.sh   # only needed once
./docker-rebuild.sh
```

**Test Accounts:** The following accounts are created automatically for testing:

| Name              | Email                      | Password   | Role  |
|-------------------|----------------------------|------------|-------|
| Alice Admin       | alice.admin@example.test   | password   | admin |
| Bob Builder       | bob@example.test           | password   | user  |
| Cara Contributor  | cara@example.test          | password   | user  |
| Dan Donor         | dan@example.test           | password   | user  |

**Additional Resources:**
- 📹 **Video Demo** — [Check the video walkthrough](#) _(add your link here)_
- 📄 **Architecture Overview** — See [`OVERVIEW.md`](OVERVIEW.md) for detailed documentation
- 🔄 **Data Flow Diagram** — View [`docs/flow.svg`](docs/flow.svg) for a visual representation of the request flow

**What the script does:**

This script will:
- Stop and remove any existing containers and volumes for this project
- Remove the local SQLite file at `database/database.sqlite`
- Rebuild the images and start the containers
- Run the Node build step (`npm run build`) inside the `node` service

When it finishes, the app will be available at:

- http://localhost:8080

> ⚠️ Note: `docker-rebuild.sh` is **destructive** to local data. It removes the SQLite database and volumes, so you will lose any locally stored records each time you run it.

---

## Scenario 2 — Local development (without Docker)

If you prefer to run everything directly on your machine (for example, when hacking on PHP or Vue without Docker), use the steps below.

### Prerequisites

Make sure you have the following installed:
- **PHP 8.3+** with SQLite extension
- **Composer** (PHP package manager)
- **Node.js 16+** and **npm**
- **Git**

### Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone <your-repo-url> acme-donations
   cd acme-donations
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies:**
   ```bash
   npm install
   ```

4. **Set up environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Set up database:**
   ```bash
   php artisan migrate
   ```

### Running the Project

You need to run **both** servers for the application to work properly:

1. **Start Laravel server** (in one terminal):
   ```bash
   php artisan serve
   ```
   This will run at: `http://127.0.0.1:8000`

2. **Start Vite dev server** (in another terminal):
   ```bash
   npm run dev
   ```
   This will run at: `http://localhost:5173`

3. **Access the application:**
   Open your browser and go to `http://127.0.0.1:8000`

### Tech Stack

- **Backend:** Laravel 12.40.1 (PHP 8.3.28)
- **Frontend:** Vue.js 3.5.25 with Inertia.js
- **Styling:** Tailwind CSS 3.2.1
- **Build Tool:** Vite 7.0.7
- **Authentication:** Laravel Breeze
- **Database:** SQLite (development)

### Troubleshooting

**If you encounter SQLite driver errors:**
```bash
# Ubuntu/Debian
sudo apt install php8.3-sqlite3

# Or install PHP SQLite extension for your system
```

**If you get Vite/Vue plugin conflicts:**
```bash
npm install --save-dev @vitejs/plugin-vue@^6.0.0 --legacy-peer-deps
```

**For any dependency issues:**
```bash
composer install
npm install
```

### Optional: Switch from SQLite to MySQL

If you prefer to use MySQL in development or production, follow these steps.

1. Uninstall any broken MySQL/MariaDB installs (optional, only if you have broken state):
```bash
sudo systemctl stop mysql || true
sudo apt remove --purge mysql-server mysql-client mysql-common mysql-server-core-* mysql-client-core-* -y || true
sudo rm -rf /var/lib/mysql /etc/mysql
sudo apt autoremove -y
```

2. Install MySQL server and client:
```bash
sudo apt update
sudo apt install mysql-server mysql-client -y
```

3. Install PHP MySQL extension (for PHP 8.3):
```bash
sudo apt install php8.3-mysql -y
```

4. Enable and start MySQL, then create the database:
```bash
sudo systemctl enable --now mysql
sudo mysql -u root -e "CREATE DATABASE acme_donations;"
sudo mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'root';"
sudo mysql -u root -p -e "FLUSH PRIVILEGES;"
```

5. Update your `.env` file to use MySQL (using root for simplicity):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=acme_donations
DB_USERNAME=root
DB_PASSWORD=root
```

6. Clear config cache and run migrations:
```bash
php artisan config:clear
php artisan migrate:fresh
```

7. If you run into service/startup issues where MySQL reports a "FROZEN" state, remove the flag and reinstall cleanly:
```bash
sudo rm -f /etc/mysql/FROZEN
# then repeat uninstall/reinstall steps above
```


