# Diplomarbeit_Massenbewegungen

A web plattform for visualizing mass movements. Built as a diploma thesis project in cooperation with Vorarlberg's Survey State Office (LVG), it provides survey engineers and geologists with tools to import geodetic measurements, visualize point displacements on interactive maps, and track movement over time.

## Features

- **Interactive displacement map** with Leaflet.js: view scaled displacement vectors per measurement point or gait lines
- **Displacement charts** with ECharts: show movement history across all measurement epochs
- **Project management**: organize project monitoring sites with metadata, contact persons, and measurement intervals
- **Role-based access control**: admin, expert, and guest roles with configurable permissions
- **Registration approval workflow**: users request access, admins approve with a role assignment
- **PostGIS-backed spatial data**: coordinate storage and transformation (MGI/Austria GK West <-> WGS84)
- **Comment system**: add comments and information to individual measurement epochs

## Tech Stack

| Layer           | Technology                                |
| --------------- | ----------------------------------------- |
| Backend         | Laravel 13, PHP 8.5, PostgreSQL + PostGIS |
| Frontend        | Vue 3, Inertia.js v3, Tailwind CSS v4     |
| Maps            | Leaflet.js with VOGIS WMS layers          |
| Charts          | Apache ECharts via vue-echarts            |
| Testing         | Pest 4 (PHP), Vitest (TypeScript)         |
| Dev Environment | Laravel Sail (Docker)                     |

## Requirements

- Docker
- Linux or WSL

## Installation

using Linux or WSL:

```bash
git clone https://github.com/ederjos/Diplomarbeit_Massenbewegungen.git
cd Diplomarbeit_Massenbewegungen
./setup.sh
```

The setup script will build the Docker containers, install dependencies, generate an application key, run migrations, and seed the database. This may take a few minutes on the first run.

## Usage

The application will be available at http://localhost.

Default admin credentials after seeding:

- Email: `josef.eder@student.htl-rankweil.at`
- Password: `secret`

### Start the application

```bash
./start.sh
```

### Stop the application

```bash
# Stop npm dev server (Ctrl+C), then:
./stop.sh
```

### After Pulling Updates

If a pull includes database changes (new migrations or seeders), reset and re-seed:

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

## Development

### Run tests

```bash
./test.sh
```

### Lint and format

```bash
./lint.sh
```

## License

MIT - see [LICENSE](./LICENSE) for details.
