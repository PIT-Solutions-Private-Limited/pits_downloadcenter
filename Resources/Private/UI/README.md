# DownloadCenter

This project runs on Angular 21 LTS (supported until May 2027).
It was migrated from Angular 7 in July 2026 (fresh scaffold + code port, standalone components).
Angular 22.0.x was tried first but has an open zone-based change detection regression
(UI stops updating after HTTP responses, https://github.com/angular/angular/issues/69530).
Before upgrading to 22+, verify that issue is fixed.

## TYPO3 integration

The production build writes ES-module bundles directly to
`Resources/Public/Scripts/angular/` (`main.js` + `polyfills.js`, no hashing, no `runtime.js`).
Those file names are referenced in `Configuration/TypoScript/setup.typoscript`
(`page.includeJSFooter` with `type = module`) — keep them in sync if you change
`outputPath` or `outputHashing` in `angular.json`.

Requires Node.js >= 22.12 (pinned to 22.22 in ddev for future Angular 22+). In this project's ddev setup the
version is pinned via `nodejs_version` in `.ddev/config.yaml`, so run all
commands inside the container:

```bash
ddev exec --dir /var/www/html/packages/pits_downloadcenter/Resources/Private/UI npm install
ddev exec --dir /var/www/html/packages/pits_downloadcenter/Resources/Private/UI npm run build
ddev exec --dir /var/www/html/packages/pits_downloadcenter/Resources/Private/UI npm test
```

The app is bootstrapped on TYPO3 pages via `<app-root>` plus hidden inputs
(`#baseURL`, `#loaderimageuri`, `#actionURL`) rendered by
`Resources/Private/Templates/Download/List.html`.

## Development server

To start a local development server, run:

```bash
ng serve
```

Once the server is running, open your browser and navigate to `http://localhost:4200/`. The application will automatically reload whenever you modify any of the source files.

## Code scaffolding

Angular CLI includes powerful code scaffolding tools. To generate a new component, run:

```bash
ng generate component component-name
```

For a complete list of available schematics (such as `components`, `directives`, or `pipes`), run:

```bash
ng generate --help
```

## Building

To build the project run:

```bash
ng build
```

This will compile your project and store the build artifacts in the `dist/` directory. By default, the production build optimizes your application for performance and speed.

## Running unit tests

To execute unit tests with the [Vitest](https://vitest.dev/) test runner, use the following command:

```bash
ng test
```

## Running end-to-end tests

For end-to-end (e2e) testing, run:

```bash
ng e2e
```

Angular CLI does not come with an end-to-end testing framework by default. You can choose one that suits your needs.

## Additional Resources

For more information on using the Angular CLI, including detailed command references, visit the [Angular CLI Overview and Command Reference](https://angular.dev/tools/cli) page.
