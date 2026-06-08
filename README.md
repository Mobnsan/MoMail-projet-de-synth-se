# MoMail

MoMail is a full-featured email campaign management application. It allows you to upload contacts, create and edit custom email templates with variables, and schedule mass email campaigns with ease.

## Features

- **Contact Management**: Upload via CSV/XLSX, group contacts, and store custom dynamic fields.
- **Advanced Templates**: Create reusable email templates with dynamic variables like `{{name}}` or `{{company}}`.
- **Campaign System**: Schedule and send batch emails to your selected contacts.
- **Queueing & Batching**: Emails are sent asynchronously in batches to respect API rate limits.
- **Multiple Providers**: Connect to SendGrid, Mailgun, or any SMTP server seamlessly.
- **User Authentication**: Secure user accounts so each user has their own isolated workspace and contacts.

## Tech Stack

- **Frontend**: React (Single Page Application)
- **Backend**: Laravel 12
- **Database**: MySQL

## Setup Instructions

1. **Clone the repository**:
   ```bash
   git clone https://github.com/Mobnsan/MoMail-projet-de-synth-se.git
   cd MoMail-projet-de-synth-se
   ```

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Environment Setup**:
   Copy the example environment file and generate an application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**:
   Create a local MySQL database named `momail`. Then, update your `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=momail
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   Run the migrations to create the tables:
   ```bash
   php artisan migrate
   ```

5. **Start the Development Server**:
   ```bash
   php artisan serve
   ```

   You can now access the application at `http://localhost:8000`.

## License

This project is open-source and available under the [MIT license](https://opensource.org/licenses/MIT).
