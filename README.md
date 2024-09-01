# Deepersignals code challenge

## Description

A demo Symfony project for qualification purposes.


## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)


## Requirements

List the software and versions required to run the project, such as:

- PHP version (e.g., PHP 8.0 or higher) was installed
- Composer was installed
- Symfony version Symfony 6.4

## Installation

Step-by-step instructions on how to install and set up the project locally:

1. **Clone the Repository:**

   ```bash
   git clone https://github.com/daibbcvl/interview.git

2. **Install dependencies:**

 ```bash
   cd interview
   composer install
  ```

## Configuration

1. **Setup API token key:**
   By default the API_TOKEN key was set as `API_TOKEN=22697ac7256dfba2c72cd00e17d9d4f38eb9d41ecd83c9e08b417bb8969d47f2
   ` in .env file, you can override it by create .env.local and set new value for `API_TOKEN`

2. **Start the sever:**

 ```bash
    symfony server:start
  ```

## Usage

- Navigate to http://localhost:8000/
- To use the upload endpoint 
```bash
curl --location 'http://localhost:8000/api/teams/upload' \
--header 'Authorization: Bearer {TOKEN}' \
--form 'csv_file=@"{PATH_TO_CSV_FILE}"'
```



