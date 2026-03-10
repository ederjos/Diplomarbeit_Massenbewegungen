# Diplomarbeit_Massenbewegungen
Diplomarbeit als Web-Plattform zur Visualisierung von Massenbewegungen

## Installation

Auf Linux or via WSL:
1. Repo clonen `git clone https://github.com/ederjos/Diplomarbeit_Massenbewegungen.git`.
2. In das neue Verzeichnis wechseln `cd Diplomarbeit_Massenbewegungen`.
3. Docker starten.
4. Bash-Skript `setup.sh` ausführen. Dies kann einige Zeit in Anspruch nehmen.

Zum Starten: `./start.sh` ausführen  
Zum Stoppen: `./start.sh` stoppen sowie `./stop.sh` ausführen

## Nach dem Pullen

Manchmal wird bei Commits die Datenbank bearbeitet. Danach einfach diesen Befehl ausführen:  
`./vendor/bin/sail artisan migrate:fresh --seed`

## TBD/TODO

### Technical Debt

* **Bezugsepoche GUI**: Admin/Editor soll Bezugsepoche pro Projekt setzen können (Settings-Seite)
* **Punktesichtbarkeit**: Einzelne Messpunkte ein-/ausblenden (für Admin)
* **Transformationen pro Punkt**: GUI zum Festlegen ob Addition, Projektion oder beides pro Punkt
* **CSV-Import im Web-Interface**: Import von Messdaten

* **Kommentare** hinzufügen, bearbeiten
* je Projekt: **Foto anzeigen**; wo: unter Orthofoto oder unter ProjectTimeline

###  Refactor

* phpDocumentor

* change db password

* Harden Models

* Return types, docstrings?

* test coverage?

## Used Design Patterns

### Backend (PHP / Laravel)

| Pattern                              | Where / How                                                                                                                                                                                                    |
| ------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Observer Pattern                     | `Addition::booted()` recomputes geometry on `saved`; `MeasurementValue::booted()` auto-calculates `geom` on `saving`. Model lifecycle hooks trigger side-effects without caller awareness.                     |
| Service Layer                        | `DisplacementCalculationService` encapsulates all displacement math (`computeForPair`, `computeAll`, `computeDisplacement`, `preloadMeasurementValues`, `computeAxisVectors`), keeping controllers thin.       |
| Dependency Injection                 | `ProjectController` receives `DisplacementCalculationService` via constructor property promotion, resolved automatically by the Laravel IoC Container.                                                         |
| Form Request Validation              | 5 dedicated `FormRequest` classes (`ApproveRegistrationRequest`, `StoreProjectRequest`, `StoreRegistrationRequest`, `SyncMeasurementsRequest`, `UpdateProjectRequest`) separate validation from controllers.    |
| API Resource (DTO)                   | 7 Eloquent API Resources (`CommentResource`, `MeasurementResource`, `MeasurementValueResource`, `PointResource`, `ProjectResource`, `ProjectShowResource`, `UserResource`) transform models for the frontend.  |
| Middleware Pipeline                  | 4 custom middleware classes (`EnsureAdminPermissions`, `EnsureMeasurementManagementPermission`, `EnsureProjectManagementPermission`, `EnsureProjectMember`) enforce role-based access in a chain.               |
| Template Method                      | `HandleInertiaRequests` extends `Inertia\Middleware` and overrides the `share()` and `version()` hook methods to inject auth data into every Inertia response.                                                 |
| Query Scopes                         | `Project` defines 2 scopes (`scopeWithLastAndNextMeasurementDate`, `scopeWithFirstAndLastMeasurementDate`); `MeasurementValue` defines `scopeWithLatLonAndOrderedByDate` — reusable, composable query logic.   |
| Soft Deletes                         | `Project` uses the `SoftDeletes` trait for non-destructive deletion and potential future restore functionality.                                                                                                 |
| Factory Pattern                      | All 14 models have corresponding Factory classes (via `HasFactory` trait) for realistic test data generation and seeding.                                                                                       |
| Implicit Route Model Binding         | Controllers type-hint Eloquent models (`Project $project`, `RegistrationRequest $registrationRequest`) so Laravel auto-resolves them from route parameters.                                                    |
| Post/Redirect/Get (PRG)              | All mutation endpoints (`approve`, `reject`, `store`, `delete`, `toggleFavorite`) return `RedirectResponse`, preventing duplicate form submissions on browser refresh.                                          |
| Domain Logic in Models               | `Projection::projectDisplacement()` computes the scalar dot product; `MeasurementValue::computeGeom()` applies coordinate offsets. Business rules live close to the data they operate on.                      |

### Frontend (Vue / TypeScript / Inertia)

| Pattern                              | Where / How                                                                                                                                                                                                    |
| ------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Composable Pattern                   | `useLeafletMap` encapsulates the full Leaflet map lifecycle (init, draw, zoom, cleanup via `onUnmounted`). `useSortableData<T>` provides generic, type-safe sorting with a 3-state cycle.                      |
| Slot-based Layout                    | `AuthenticatedLayout` provides a shared header/nav and a `<slot />` where each page injects its own content — classic Vue layout pattern.                                                                      |
| Component Composition                | Pages (`Home`, `Project`, `Admin`) compose domain-specific components inside a shared layout. `Project.vue` further composes `TabSwitcher` + conditional `ResultsTab`/`DetailsTab` via `v-show`.                |
| Domain-Driven Component Organization | Components are grouped by feature domain: `admin/`, `auth/`, `chart/`, `map/`, `measurement/`, `project/`, `ui/` (generic reusables).                                                                         |
| Type System / Interface Hierarchy    | `@types/` directory defines extending interfaces (`BasePoint` → `Point`, `BaseMeasurement` → `Measurement`). Typed props flow from Inertia server responses through to leaf components.                        |
| Centralized Configuration            | `config/mapConstants.ts`, `config/mapLayers.ts`, `config/colors.ts` eliminate magic numbers and strings across all frontend components.                                                                         |
| Wayfinder Typed Route Functions      | Backend routes are imported as typed TypeScript functions (`import { index as home } from '@/actions/...'`), replacing manual URL strings with compile-time checked route generation.                            |
| Co-located Tests                     | Frontend test files sit next to their components (e.g. `SortableHeader.test.ts` beside `SortableHeader.vue`). Backend Pest tests are organized into `Feature/` and `Unit/` directories.                        |


## Notes

magellan.php config lists SRID 31254 as "geodetic" — technically MGI/Austria GK West is a projected CRS, not geodetic. This may cause subtle issues with distance calculations
Empty Support and Traits directories should be cleaned up or used
The CommentsTableSeeder hardcodes measurement_id => 28 which is fragile

## Changes

### 04.11.2025 column rename

Let's rename the column 'interval' of type `INTERVAL` to 'period'
- Changed in PHP -> [x]
- Changed in ERM -> [x]

### 18.11.2025 naming a measurement

Let's add a column 'name' to the table `MEASUREMENT`
- Changed in Migration -> [x]
- Changed in Seeder    -> [x]
- Changed in ERM       -> [x]

## To be determined:

* max zoom for maps (schummerung, ...)
* which maps are needed


## Für den schriftlichen Teil

* Überschrift mit Text: "Im Kapitel wird das ....-Modul beschrieben. In 17.1 gibt's 'nen Überblick, in 17.2 geht's um ..."
* Anhand Inhaltsverzeichnis schon roten Faden, überlegen was wo drin ist.
* Abb mit Erklärung: auch mehrere Zeilen (erklären)
* JEDE Abb MUSS IM TEXT ERWÄHNT WERDEN! (Im Fließtext)
* Unten steht, wer geschrieben hat
* Keine Stiefmütterchen
* Abb unten beschriften, Tabellen oben (letztere können sehr lang werden, Zsf zuerst macht mehr Sinn; Grafik sowieso -> darunter)
* Relevante Ausschnitte aus dem Code (wie Authentifizierung lösen -> relevante Teile) KEINE Screenshots mit Darkmode (Lightmode & Text)
* Querformat nach Möglichkeit vermeiden
* Über Software-Patterns schreiben


## notes

* import L from leaflet -> bad (tree shaking)
* leaflet latest release very old


