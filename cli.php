<?php

declare(strict_types=1);

$root = __DIR__;
$command = $argv[1] ?? 'help';
$arguments = array_slice($argv, 2);

require $root . '/vendor/autoload.php';

Dotenv\Dotenv::createImmutable($root)->safeLoad();

try {
    match ($command) {
        'install' => installDatabase($root),
        'publish-scheduled' => publishScheduledEpisodes($root),
        'hydrate-images' => hydrateImages($root, $arguments),
        'regenerate-image-variants' => regenerateImageVariants($root, $arguments),
        'create-controller' => createController($root, $arguments),
        'create-middleware' => createMiddleware($root, $arguments),
        'create-service' => createService($root, $arguments),
        'create-starter-project', '--create-starter-project' => createStarterProject($root, $arguments),
        'pull-starter-project', '--pull-starter-project' => pullStarterProject($root, $arguments),
        'help', '--help', '-h' => showHelp(),
        default => unknownCommand($command),
    };
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}

function showHelp(): void
{
    echo "Paper-PHPFramework CLI\n";
    echo "Usage:\n";
    echo "  php cli.php install                                      Create the database and import database.sql\n";
    echo "  php cli.php publish-scheduled                            Publish episodes whose air date has passed\n";
    echo "  php cli.php hydrate-images [--scope=all] [--limit=50] [--loop]\n";
    echo "      Backfill missing local WebP poster/backdrop variants from TMDB URLs\n";
    echo "  php cli.php regenerate-image-variants [--scope=all] [--limit=50] [--loop]\n";
    echo "      Regenerate width variants for existing local poster/backdrop files\n";
    echo "  php cli.php create-controller --UserController           Create and register UserController/UserController.php\n";
    echo "  php cli.php create-controller --UserController --ProfileController\n";
    echo "      Create and register UserController/ProfileController.php\n";
    echo "  php cli.php create-controller --folder=UserController --name=ProfileController\n";
    echo "  php cli.php create-controller UserController --route=/users\n";
    echo "  php cli.php create-controller --folder=FolderName --ControllerName --route=/route/to\n";
    echo "  php cli.php create-middleware --AuditMiddleware      Create and register middleware\n";
    echo "  php cli.php create-service --BillingService          Create and register a service\n";
    echo "  php cli.php --create-starter-project                 Reset app controllers, views, routes, starter assets, and app definitions\n";
    echo "  php cli.php --create-starter-project --dry-run       Preview the starter reset without deleting files\n";
    echo "  php cli.php --create-starter-project --force         Reset without the confirmation prompt\n";
    echo "  php cli.php --pull-starter-project --repo=owner/repo --name=my-app\n";
    echo "      Clone a GitHub starter repository into a named project folder\n";
    echo "  php cli.php --pull-starter-project --repo=https://github.com/owner/repo.git --name=my-app --keep-git\n";
    echo "  php cli.php --pull-starter-project --repo=owner/repo --name=my-app --starter-only\n";
    echo "  php cli.php help                                         Show this help message\n";
}

function unknownCommand(string $command): never
{
    fwrite(STDERR, "Unknown command: {$command}\n");
    fwrite(STDERR, "Run php cli.php help for available commands.\n");
    exit(1);
}

function createController(string $root, array $arguments): void
{
    [$folderName, $controllerName, $dryRun, $routePath, $httpMethod] = controllerCommandOptions($arguments);

    if ($controllerName === null) {
        throw new InvalidArgumentException(
            'Missing controller name. Example: php cli.php create-controller --UserController'
        );
    }

    $folderName = normalizeControllerName($folderName ?? $controllerName);
    $controllerName = normalizeControllerName($controllerName);
    $folderBaseName = controllerBaseName($folderName);
    $controllerBaseName = controllerBaseName($controllerName);
    $methodName = controllerMethodName($folderBaseName, $controllerBaseName);
    $propertyName = $methodName;
    $routePath = normalizeRoutePath($routePath ?? controllerRoutePath($folderBaseName, $controllerBaseName));
    $httpMethod = normalizeHttpMethod($httpMethod);
    $viewPath = controllerViewPath($folderBaseName, $controllerBaseName);
    $layoutPath = controllerDefaultLayout();
    $viewTitle = controllerTitle($folderBaseName, $controllerBaseName);
    $bodyClass = controllerBodyClass($viewPath);
    $targetDirectory = $root . "/src/App/Controllers/{$folderName}";
    $targetFile = "{$targetDirectory}/{$controllerName}.php";
    $viewFile = $root . "/src/App/views/{$viewPath}.php";
    $viewDirectory = dirname($viewFile);
    $appControllerPath = $root . '/src/App/Controllers/AppController.php';
    $routesPath = $root . '/src/App/Config/Routes.php';
    $containerDefinitionsPath = $root . '/src/App/container-definitions.php';

    if (is_file($targetFile)) {
        throw new RuntimeException("Controller already exists: {$targetFile}");
    }

    if (is_file($viewFile)) {
        throw new RuntimeException("View already exists: {$viewFile}");
    }

    $shouldCreateAppController = !is_file($appControllerPath);
    $appControllerContents = $shouldCreateAppController
        ? appControllerStub()
        : null;
    $updatedAppController = registerControllerInAppControllerContents(
        $appControllerContents ?? readFileOrFail($appControllerPath, 'AppController'),
        $folderName,
        $controllerName,
        $propertyName,
        $methodName
    );
    $updatedRoutes = registerControllerRoute($routesPath, $httpMethod, $routePath, $methodName);
    $updatedContainerDefinitions = registerControllerDefinition($containerDefinitionsPath, $folderName, $controllerName);

    if ($dryRun) {
        echo "Controller would be created at:\n";
        echo "  {$targetFile}\n";
        echo "View would be created at:\n";
        echo "  {$viewFile}\n";
        echo "Controller would render:\n";
        echo "  {$viewPath} with {$layoutPath} and title \"{$viewTitle}\"\n";

        if ($shouldCreateAppController) {
            echo "AppController would be created at:\n";
            echo "  {$appControllerPath}\n";
        }

        echo "AppController would add:\n";
        echo "  {$methodName}() -> {$controllerName}::index()\n";
        echo "Route would add:\n";
        echo "  {$httpMethod} {$routePath}\n";
        echo "Container definitions would add:\n";
        echo "  {$controllerName}::class\n";
        return;
    }

    if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true)) {
        throw new RuntimeException("Could not create controller directory: {$targetDirectory}");
    }

    if (!is_dir($viewDirectory) && !mkdir($viewDirectory, 0775, true)) {
        throw new RuntimeException("Could not create view directory: {$viewDirectory}");
    }

    if (file_put_contents($targetFile, controllerStub(
        $folderName,
        $controllerName,
        $viewPath,
        $layoutPath,
        $viewTitle,
        $bodyClass
    )) === false) {
        throw new RuntimeException("Could not write controller file: {$targetFile}");
    }

    if (file_put_contents($viewFile, viewStub($viewTitle)) === false) {
        throw new RuntimeException("Could not write view file: {$viewFile}");
    }

    if ($shouldCreateAppController && !is_dir(dirname($appControllerPath)) && !mkdir(dirname($appControllerPath), 0775, true)) {
        throw new RuntimeException("Could not create AppController directory: " . dirname($appControllerPath));
    }

    if ($updatedAppController !== null && file_put_contents($appControllerPath, $updatedAppController) === false) {
        throw new RuntimeException("Could not update AppController: {$appControllerPath}");
    }

    if (file_put_contents($routesPath, $updatedRoutes) === false) {
        throw new RuntimeException("Could not update routes file: {$routesPath}");
    }

    if (file_put_contents($containerDefinitionsPath, $updatedContainerDefinitions) === false) {
        throw new RuntimeException("Could not update container definitions: {$containerDefinitionsPath}");
    }

    echo "Controller created successfully.\n";
    echo "Path: {$targetFile}\n";
    echo "View: {$viewFile}\n";
    echo "Namespace: App\\Controllers\\{$folderName}\n";
    echo "Class: {$controllerName}\n";
    echo "Title: {$viewTitle}\n";
    echo "Layout: {$layoutPath}\n";
    echo $shouldCreateAppController
        ? "AppController created: {$appControllerPath}\n"
        : '';
    echo "AppController method: {$methodName}\n";
    echo "Route: {$httpMethod} {$routePath}\n";
    echo "Registered in: {$containerDefinitionsPath}\n";
}

function createMiddleware(string $root, array $arguments): void
{
    [$folderName, $middlewareName, $dryRun] = componentCommandOptions($arguments);

    if ($middlewareName === null) {
        throw new InvalidArgumentException(
            'Missing middleware name. Example: php cli.php create-middleware --AuditMiddleware'
        );
    }

    $middlewareName = normalizeComponentClassName($middlewareName, 'Middleware');
    $folderName = normalizeComponentFolderName($folderName ?? $middlewareName);
    $targetDirectory = $root . "/src/App/Middleware/{$folderName}";
    $targetFile = "{$targetDirectory}/{$middlewareName}.php";
    $middlewareConfigPath = $root . '/src/App/Config/Middleware.php';

    if (is_file($targetFile)) {
        throw new RuntimeException("Middleware already exists: {$targetFile}");
    }

    $updatedMiddlewareConfig = registerMiddlewareClass($middlewareConfigPath, $folderName, $middlewareName);

    if ($dryRun) {
        echo "Middleware would be created at:\n";
        echo "  {$targetFile}\n";
        echo "Middleware config would add:\n";
        echo "  {$middlewareName}::class\n";
        return;
    }

    if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true)) {
        throw new RuntimeException("Could not create middleware directory: {$targetDirectory}");
    }

    if (file_put_contents($targetFile, middlewareStub($folderName, $middlewareName)) === false) {
        throw new RuntimeException("Could not write middleware file: {$targetFile}");
    }

    if (file_put_contents($middlewareConfigPath, $updatedMiddlewareConfig) === false) {
        throw new RuntimeException("Could not update middleware config: {$middlewareConfigPath}");
    }

    echo "Middleware created successfully.\n";
    echo "Path: {$targetFile}\n";
    echo "Namespace: App\\Middleware\\{$folderName}\n";
    echo "Class: {$middlewareName}\n";
    echo "Registered in: {$middlewareConfigPath}\n";
}

function createService(string $root, array $arguments): void
{
    [$folderName, $serviceName, $dryRun] = componentCommandOptions($arguments);

    if ($serviceName === null) {
        throw new InvalidArgumentException(
            'Missing service name. Example: php cli.php create-service --BillingService'
        );
    }

    $serviceName = normalizeComponentClassName($serviceName, 'Service');
    $folderName = normalizeComponentFolderName($folderName ?? $serviceName);
    $targetDirectory = $root . "/src/App/Services/{$folderName}";
    $targetFile = "{$targetDirectory}/{$serviceName}.php";
    $containerDefinitionsPath = $root . '/src/App/container-definitions.php';

    if (is_file($targetFile)) {
        throw new RuntimeException("Service already exists: {$targetFile}");
    }

    $updatedContainerDefinitions = registerServiceDefinition($containerDefinitionsPath, $folderName, $serviceName);

    if ($dryRun) {
        echo "Service would be created at:\n";
        echo "  {$targetFile}\n";
        echo "Container definitions would add:\n";
        echo "  {$serviceName}::class\n";
        return;
    }

    if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true)) {
        throw new RuntimeException("Could not create service directory: {$targetDirectory}");
    }

    if (file_put_contents($targetFile, serviceStub($folderName, $serviceName)) === false) {
        throw new RuntimeException("Could not write service file: {$targetFile}");
    }

    if (file_put_contents($containerDefinitionsPath, $updatedContainerDefinitions) === false) {
        throw new RuntimeException("Could not update container definitions: {$containerDefinitionsPath}");
    }

    echo "Service created successfully.\n";
    echo "Path: {$targetFile}\n";
    echo "Namespace: App\\Services\\{$folderName}\n";
    echo "Class: {$serviceName}\n";
    echo "Registered in: {$containerDefinitionsPath}\n";
}

function createStarterProject(string $root, array $arguments): void
{
    [$dryRun, $force] = starterProjectOptions($arguments);

    $controllersPath = $root . '/src/App/Controllers';
    $servicesPath = $root . '/src/App/Services';
    $viewsPath = $root . '/src/App/views';
    $frontendAssetsPath = $root . '/public/assets/frontend';
    $backendAssetsPath = $root . '/public/assets/backend';
    $routesPath = $root . '/src/App/Config/Routes.php';
    $containerDefinitionsPath = $root . '/src/App/container-definitions.php';
    $appControllerPath = $controllersPath . '/AppController.php';
    $welcomeControllerPath = $controllersPath . '/WelcomeController.php';
    $frontendLayoutPath = $viewsPath . '/layouts/frontend/paper.php';
    $backendLayoutPath = $viewsPath . '/layouts/backend/paper.php';
    $welcomeViewPath = $viewsPath . '/frontend/welcome/index.php';
    $notFoundViewPath = $viewsPath . '/frontend/errors/not-found.php';
    $frontendCssPath = $frontendAssetsPath . '/css/paper.css';
    $frontendJsPath = $frontendAssetsPath . '/js/app.js';
    $backendCssPath = $backendAssetsPath . '/css/paper.css';
    $backendJsPath = $backendAssetsPath . '/js/app.js';

    $plannedActions = [
        "Delete all files and folders inside {$controllersPath}",
        "Create {$appControllerPath}",
        "Create {$welcomeControllerPath}",
        "Delete all files and folders inside {$servicesPath}",
        "Delete all files and folders inside {$viewsPath}",
        "Create Paper frontend layout at {$frontendLayoutPath}",
        "Create Paper backend layout at {$backendLayoutPath}",
        "Create welcome view at {$welcomeViewPath}",
        "Create 404 view at {$notFoundViewPath}",
        "Refresh starter frontend assets inside {$frontendAssetsPath}",
        "Refresh starter backend assets inside {$backendAssetsPath}",
        "Rewrite {$routesPath} with GET /, GET /404, route loop, and error handler",
        "Rewrite {$containerDefinitionsPath} with TemplateEngine, Database, AppController, and WelcomeController registrations",
    ];

    if ($dryRun) {
        echo "Starter project reset preview:\n";

        foreach ($plannedActions as $action) {
            echo "  - {$action}\n";
        }

        echo "No files were changed.\n";
        return;
    }

    if (!$force && !confirmStarterReset()) {
        echo "Starter project reset cancelled.\n";
        return;
    }

    ensureDirectory($controllersPath);
    ensureDirectory($servicesPath);
    ensureDirectory($viewsPath);
    ensureDirectory($frontendAssetsPath);
    ensureDirectory($backendAssetsPath);

    clearDirectory($controllersPath, $root);
    clearDirectory($servicesPath, $root);
    clearDirectory($viewsPath, $root);
    clearDirectory($frontendAssetsPath, $root);
    clearDirectory($backendAssetsPath, $root);

    $starterFiles = [
        $appControllerPath => starterAppControllerStub(),
        $welcomeControllerPath => welcomeControllerStub(),
        $frontendLayoutPath => starterFrontendLayoutStub(),
        $backendLayoutPath => starterBackendLayoutStub(),
        $welcomeViewPath => starterWelcomeViewStub(),
        $notFoundViewPath => starterNotFoundViewStub(),
        $frontendCssPath => starterFrontendCssStub(),
        $frontendJsPath => starterFrontendJsStub(),
        $backendCssPath => starterBackendCssStub(),
        $backendJsPath => starterBackendJsStub(),
        $routesPath => starterRoutesStub(),
        $containerDefinitionsPath => starterContainerDefinitionsStub(),
    ];

    foreach ($starterFiles as $path => $contents) {
        writeGeneratedFile($path, $contents);
    }

    echo "Starter project created successfully.\n";
    echo "Remaining routes: GET /, GET /404\n";
    echo "App controller: {$appControllerPath}\n";
    echo "Remaining controller: {$welcomeControllerPath}\n";
    echo "Frontend layout: {$frontendLayoutPath}\n";
    echo "Backend layout: {$backendLayoutPath}\n";
    echo "Frontend assets: {$frontendAssetsPath}\n";
    echo "Backend assets: {$backendAssetsPath}\n";
}

function pullStarterProject(string $root, array $arguments): void
{
    [$repo, $projectName, $targetBase, $branch, $keepGit, $starterOnly, $dryRun] = pullStarterProjectOptions($root, $arguments);
    $targetDirectory = $targetBase . DIRECTORY_SEPARATOR . $projectName;

    if (is_dir($targetDirectory) || is_file($targetDirectory)) {
        throw new RuntimeException("Target already exists: {$targetDirectory}");
    }

    $cloneUrl = githubCloneUrl($repo);
    $command = ['git', 'clone'];

    if (!$keepGit || $starterOnly) {
        $command[] = '--depth';
        $command[] = '1';
    }

    if ($branch !== null) {
        $command[] = '--branch';
        $command[] = $branch;
    }

    $command[] = $cloneUrl;
    $command[] = $targetDirectory;

    if ($dryRun) {
        echo "Starter project pull preview:\n";
        echo "  Repository: {$cloneUrl}\n";
        echo "  Project name: {$projectName}\n";
        echo "  Target: {$targetDirectory}\n";
        echo "  Keep git history: " . ($keepGit ? 'yes' : 'no') . "\n";
        echo "  Starter only: " . ($starterOnly ? 'yes' : 'no') . "\n";
        echo "  Clone depth: " . ((!$keepGit || $starterOnly) ? '1' : 'full') . "\n";

        if ($branch !== null) {
            echo "  Branch: {$branch}\n";
        }

        echo "No files were changed.\n";
        return;
    }

    runProcess($command, dirname($targetDirectory));

    if (!$keepGit) {
        $gitDirectory = $targetDirectory . DIRECTORY_SEPARATOR . '.git';

        if (is_dir($gitDirectory)) {
            clearDirectory($gitDirectory, $targetDirectory);

            if (!rmdir($gitDirectory)) {
                throw new RuntimeException("Could not remove git directory: {$gitDirectory}");
            }
        }
    }

    if ($starterOnly) {
        createStarterProject($targetDirectory, ['--force']);
    }

    writePulledProjectEnvName($targetDirectory, $projectName);

    echo "Starter project pulled successfully.\n";
    echo "Repository: {$cloneUrl}\n";
    echo "Project: {$projectName}\n";
    echo "Path: {$targetDirectory}\n";
}

function pullStarterProjectOptions(string $root, array $arguments): array
{
    $repo = null;
    $projectName = null;
    $targetBase = dirname($root);
    $branch = null;
    $keepGit = false;
    $starterOnly = false;
    $dryRun = false;

    foreach ($arguments as $argument) {
        if ($argument === '--keep-git') {
            $keepGit = true;
            continue;
        }

        if ($argument === '--starter-only' || $argument === '--minimal') {
            $starterOnly = true;
            continue;
        }

        if ($argument === '--dry-run') {
            $dryRun = true;
            continue;
        }

        foreach (['--repo=', '--github='] as $prefix) {
            if (str_starts_with($argument, $prefix)) {
                $repo = substr($argument, strlen($prefix));
                continue 2;
            }
        }

        foreach (['--name=', '--project=', '--project-name='] as $prefix) {
            if (str_starts_with($argument, $prefix)) {
                $projectName = substr($argument, strlen($prefix));
                continue 2;
            }
        }

        foreach (['--target=', '--path='] as $prefix) {
            if (str_starts_with($argument, $prefix)) {
                $targetBase = normalizeTargetBase(substr($argument, strlen($prefix)), $root);
                continue 2;
            }
        }

        if (str_starts_with($argument, '--branch=')) {
            $branch = substr($argument, strlen('--branch='));
            continue;
        }

        throw new InvalidArgumentException("Unknown pull starter option: {$argument}");
    }

    if ($repo === null || trim($repo) === '') {
        throw new InvalidArgumentException(
            'Missing repository. Example: php cli.php --pull-starter-project --repo=owner/repo --name=my-app'
        );
    }

    if ($projectName === null || trim($projectName) === '') {
        throw new InvalidArgumentException(
            'Missing project name. Example: php cli.php --pull-starter-project --repo=owner/repo --name=my-app'
        );
    }

    $projectName = normalizeProjectDirectoryName($projectName);

    if ($branch !== null && !preg_match('/^[A-Za-z0-9._\/-]+$/', $branch)) {
        throw new InvalidArgumentException('Branch may only contain letters, numbers, dot, slash, dash, and underscore.');
    }

    return [$repo, $projectName, $targetBase, $branch, $keepGit, $starterOnly, $dryRun];
}

function githubCloneUrl(string $repo): string
{
    $repo = trim($repo);

    if (preg_match('#^https://github\.com/[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+(?:\.git)?$#', $repo)) {
        return str_ends_with($repo, '.git') ? $repo : $repo . '.git';
    }

    if (preg_match('/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/', $repo)) {
        return "https://github.com/{$repo}.git";
    }

    throw new InvalidArgumentException(
        'Repository must be owner/repo or https://github.com/owner/repo.git.'
    );
}

function normalizeProjectDirectoryName(string $projectName): string
{
    $projectName = trim($projectName);

    if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $projectName)) {
        throw new InvalidArgumentException(
            'Project name must start with a letter or number and contain only letters, numbers, dot, dash, or underscore.'
        );
    }

    if (in_array($projectName, ['.', '..'], true)) {
        throw new InvalidArgumentException('Project name cannot be . or ...');
    }

    return $projectName;
}

function normalizeTargetBase(string $targetBase, string $root): string
{
    $targetBase = trim($targetBase);

    if ($targetBase === '') {
        throw new InvalidArgumentException('Target path cannot be empty.');
    }

    if (!preg_match('/^[A-Za-z]:[\/\\\\]/', $targetBase) && !str_starts_with($targetBase, DIRECTORY_SEPARATOR)) {
        $targetBase = $root . DIRECTORY_SEPARATOR . $targetBase;
    }

    if (!is_dir($targetBase)) {
        throw new InvalidArgumentException("Target path does not exist: {$targetBase}");
    }

    $realPath = realpath($targetBase);

    if ($realPath === false) {
        throw new RuntimeException("Could not resolve target path: {$targetBase}");
    }

    return $realPath;
}

function runProcess(array $command, string $cwd): void
{
    $descriptorSpec = [
        0 => STDIN,
        1 => STDOUT,
        2 => STDERR,
    ];

    $process = proc_open($command, $descriptorSpec, $pipes, $cwd);

    if (!is_resource($process)) {
        throw new RuntimeException('Could not start process.');
    }

    $exitCode = proc_close($process);

    if ($exitCode !== 0) {
        throw new RuntimeException('Command failed with exit code ' . $exitCode . '.');
    }
}

function writePulledProjectEnvName(string $targetDirectory, string $projectName): void
{
    $envPath = $targetDirectory . DIRECTORY_SEPARATOR . '.env';

    if (!is_file($envPath) || !is_writable($envPath)) {
        return;
    }

    $contents = file_get_contents($envPath);

    if ($contents === false) {
        return;
    }

    $appName = 'APP_NAME="' . str_replace('"', '\"', $projectName) . '"';

    if (preg_match('/^APP_NAME=.*$/m', $contents)) {
        $contents = preg_replace('/^APP_NAME=.*$/m', $appName, $contents, 1) ?? $contents;
    } else {
        $contents .= "\n{$appName}\n";
    }

    file_put_contents($envPath, $contents);
}

function starterProjectOptions(array $arguments): array
{
    $dryRun = false;
    $force = false;

    foreach ($arguments as $argument) {
        match ($argument) {
            '--dry-run' => $dryRun = true,
            '--force', '-f' => $force = true,
            default => throw new InvalidArgumentException("Unknown starter option: {$argument}"),
        };
    }

    return [$dryRun, $force];
}

function confirmStarterReset(): bool
{
    echo "This will delete app controllers, services, views, and starter frontend/backend assets, then reset routes and app container definitions.\n";
    echo "Type CREATE STARTER to continue: ";

    $answer = trim((string) fgets(STDIN));

    return $answer === 'CREATE STARTER';
}

function ensureDirectory(string $path): void
{
    if (is_dir($path)) {
        return;
    }

    if (!mkdir($path, 0775, true)) {
        throw new RuntimeException("Could not create directory: {$path}");
    }
}

function clearDirectory(string $path, string $root): void
{
    $rootPath = realpath($root);
    $targetPath = realpath($path);

    if ($rootPath === false || $targetPath === false) {
        throw new RuntimeException("Could not resolve path for cleanup: {$path}");
    }

    if (!str_starts_with($targetPath, $rootPath . DIRECTORY_SEPARATOR)) {
        throw new RuntimeException("Refusing to clean path outside project root: {$path}");
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($targetPath, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        $itemPath = $item->getPathname();

        if ($item->isDir()) {
            if (!rmdir($itemPath)) {
                throw new RuntimeException("Could not remove directory: {$itemPath}");
            }

            continue;
        }

        if (!unlink($itemPath)) {
            throw new RuntimeException("Could not remove file: {$itemPath}");
        }
    }
}

function writeGeneratedFile(string $path, string $contents): void
{
    $directory = dirname($path);

    if (!is_dir($directory) && !mkdir($directory, 0775, true)) {
        throw new RuntimeException("Could not create directory: {$directory}");
    }

    if (file_put_contents($path, $contents) === false) {
        throw new RuntimeException("Could not write file: {$path}");
    }
}

function componentCommandOptions(array $arguments): array
{
    $folderName = null;
    $className = null;
    $dryRun = false;
    $bareNames = [];

    foreach ($arguments as $argument) {
        if ($argument === '--dry-run') {
            $dryRun = true;
            continue;
        }

        foreach (['--folder=', '--directory=', '--module='] as $prefix) {
            if (str_starts_with($argument, $prefix)) {
                $folderName = substr($argument, strlen($prefix));
                continue 2;
            }
        }

        foreach (['--name=', '--class=', '--middleware=', '--service=', '--MiddlewareName=', '--ServiceName='] as $prefix) {
            if (str_starts_with($argument, $prefix)) {
                $className = substr($argument, strlen($prefix));
                continue 2;
            }
        }

        if (str_starts_with($argument, '-')) {
            $name = ltrim($argument, '-');

            if ($name !== '') {
                $bareNames[] = $name;
            }

            continue;
        }

        $bareNames[] = $argument;
    }

    if ($bareNames !== []) {
        if ($folderName === null && count($bareNames) > 1) {
            $folderName = $bareNames[0];
            $className ??= $bareNames[1];
        } else {
            $className ??= $bareNames[0];
        }
    }

    if ($className === null && $folderName !== null) {
        $className = $folderName;
    }

    return [$folderName, $className, $dryRun];
}

function normalizeComponentClassName(string $className, string $suffix): string
{
    $className = trim($className);
    $className = preg_replace('/\.php$/i', '', $className) ?? $className;

    if (!str_ends_with($className, $suffix)) {
        $className .= $suffix;
    }

    if (!preg_match('/^[A-Z][A-Za-z0-9_]*$/', $className)) {
        throw new InvalidArgumentException(
            "{$suffix} name must start with an uppercase letter and contain only letters, numbers, or underscores."
        );
    }

    return $className;
}

function normalizeComponentFolderName(string $folderName): string
{
    $folderName = trim($folderName);
    $folderName = preg_replace('/\.php$/i', '', $folderName) ?? $folderName;

    if (!preg_match('/^[A-Z][A-Za-z0-9_]*$/', $folderName)) {
        throw new InvalidArgumentException(
            'Folder name must start with an uppercase letter and contain only letters, numbers, or underscores.'
        );
    }

    return $folderName;
}

function controllerCommandOptions(array $arguments): array
{
    $folderName = null;
    $controllerName = null;
    $dryRun = false;
    $routePath = null;
    $httpMethod = 'GET';
    $bareNames = [];

    foreach ($arguments as $argument) {
        if ($argument === '--dry-run') {
            $dryRun = true;
            continue;
        }

        foreach (['--route=', '--path='] as $prefix) {
            if (str_starts_with($argument, $prefix)) {
                $routePath = substr($argument, strlen($prefix));
                continue 2;
            }
        }

        if (str_starts_with($argument, '--method=')) {
            $httpMethod = substr($argument, strlen('--method='));
            continue;
        }

        foreach (['--folder=', '--directory=', '--module='] as $prefix) {
            if (str_starts_with($argument, $prefix)) {
                $folderName = substr($argument, strlen($prefix));
                continue 2;
            }
        }

        foreach (['--name=', '--controller=', '--ControllerName=', '--class='] as $prefix) {
            if (str_starts_with($argument, $prefix)) {
                $controllerName = substr($argument, strlen($prefix));
                continue 2;
            }
        }

        if (str_starts_with($argument, '-')) {
            $name = ltrim($argument, '-');

            if ($name !== '') {
                $bareNames[] = $name;
            }

            continue;
        }

        $bareNames[] = $argument;
    }

    if ($bareNames !== []) {
        if ($folderName === null && $controllerName === null) {
            $folderName = $bareNames[0];
            $controllerName = $bareNames[1] ?? $bareNames[0];
        } elseif ($folderName === null) {
            $folderName = $bareNames[0];
        } elseif ($controllerName === null) {
            $controllerName = $bareNames[0];
        }
    }

    if ($folderName === null && $controllerName !== null) {
        $folderName = $controllerName;
    }

    if ($controllerName === null && $folderName !== null) {
        $controllerName = $folderName;
    }

    return [$folderName, $controllerName, $dryRun, $routePath, $httpMethod];
}

function normalizeControllerName(string $controllerName): string
{
    $controllerName = trim($controllerName);
    $controllerName = preg_replace('/\.php$/i', '', $controllerName) ?? $controllerName;

    if (!preg_match('/^[A-Z][A-Za-z0-9_]*$/', $controllerName)) {
        throw new InvalidArgumentException(
            'Controller name must start with an uppercase letter and contain only letters, numbers, or underscores.'
        );
    }

    return $controllerName;
}

function pluralizeResourceName(string $name): string
{
    if (preg_match('/[^aeiou]y$/i', $name)) {
        return substr($name, 0, -1) . 'ies';
    }

    if (preg_match('/(s|x|z|ch|sh)$/i', $name)) {
        return $name . 'es';
    }

    return $name . 's';
}

function controllerBaseName(string $controllerName): string
{
    $baseName = preg_replace('/Controller$/', '', $controllerName) ?? $controllerName;

    return $baseName !== '' ? $baseName : $controllerName;
}

function controllerMethodName(string $folderBaseName, string $controllerBaseName): string
{
    if ($folderBaseName === $controllerBaseName) {
        return lcfirst($controllerBaseName);
    }

    return lcfirst($folderBaseName . ucfirst($controllerBaseName));
}

function controllerRoutePath(string $folderBaseName, string $controllerBaseName): string
{
    $folderSlug = routeSlug($folderBaseName);

    if ($folderBaseName === $controllerBaseName) {
        return '/' . $folderSlug;
    }

    return '/' . $folderSlug . '/' . routeSlug($controllerBaseName);
}

function routeSlug(string $name): string
{
    $slug = preg_replace('/(?<!^)[A-Z]/', '-$0', $name) ?? $name;
    $slug = str_replace('_', '-', $slug);

    return strtolower($slug);
}

function controllerViewPath(string $folderBaseName, string $controllerBaseName): string
{
    $folderPath = routeSlug($folderBaseName);

    if ($folderBaseName === $controllerBaseName) {
        return "frontend/{$folderPath}/index";
    }

    return "frontend/{$folderPath}/" . routeSlug($controllerBaseName) . '/index';
}

function controllerDefaultLayout(): string
{
    return 'layouts/frontend/paper';
}

function controllerTitle(string $folderBaseName, string $controllerBaseName): string
{
    $words = titleWords($folderBaseName);

    if ($folderBaseName !== $controllerBaseName) {
        $words .= ' ' . titleWords($controllerBaseName);
    }

    return $words;
}

function titleWords(string $name): string
{
    $words = preg_replace('/(?<!^)[A-Z]/', ' $0', $name) ?? $name;
    $words = str_replace('_', ' ', $words);

    return trim($words);
}

function controllerBodyClass(string $viewPath): string
{
    $page = preg_replace('#/index$#', '', $viewPath) ?? $viewPath;
    $page = preg_replace('#^(frontend|backend)/#', '', $page) ?? $page;

    return 'paper-' . str_replace('/', '-', $page);
}

function normalizeRoutePath(string $routePath): string
{
    $routePath = '/' . trim($routePath, '/');
    $routePath = preg_replace('#/+#', '/', $routePath) ?? $routePath;

    if (!preg_match('#^/[A-Za-z0-9/_-]*$#', $routePath)) {
        throw new InvalidArgumentException('Route path may only contain letters, numbers, slash, dash, and underscore.');
    }

    return $routePath;
}

function normalizeHttpMethod(string $httpMethod): string
{
    $httpMethod = strtoupper(trim($httpMethod));
    $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    if (!in_array($httpMethod, $allowedMethods, true)) {
        throw new InvalidArgumentException('HTTP method must be GET, POST, PUT, PATCH, or DELETE.');
    }

    return $httpMethod;
}

function registerControllerInAppController(
    string $appControllerPath,
    string $folderName,
    string $controllerName,
    string $propertyName,
    string $methodName
): string {
    return registerControllerInAppControllerContents(
        readFileOrFail($appControllerPath, 'AppController'),
        $folderName,
        $controllerName,
        $propertyName,
        $methodName
    );
}

function registerControllerInAppControllerContents(
    string $contents,
    string $folderName,
    string $controllerName,
    string $propertyName,
    string $methodName
): string {
    if (preg_match('/function\s+' . preg_quote($methodName, '/') . '\s*\(/', $contents)) {
        throw new RuntimeException("AppController method already exists: {$methodName}");
    }

    $useStatement = "use App\\Controllers\\{$folderName}\\{$controllerName};";

    if (!str_contains($contents, $useStatement)) {
        $contents = str_replace(
            "use Framework\\Http\\Request;",
            "{$useStatement}\nuse Framework\\Http\\Request;",
            $contents
        );
    }

    $constructorParameter = "        private {$controllerName} \${$propertyName}";

    if (!str_contains($contents, $constructorParameter)) {
        if (preg_match('/public function __construct\(\R\s*\) \{/', $contents)) {
            $contents = preg_replace(
                '/public function __construct\(\R\s*\) \{/',
                "public function __construct(\n{$constructorParameter}\n    ) {",
                $contents,
                1,
                $replacements
            );
        } else {
            $contents = preg_replace_callback(
                '/public function __construct\(\R(?<params>.*?)\R    \) \{/s',
                function (array $matches) use ($constructorParameter): string {
                    $params = rtrim($matches['params']);

                    if ($params !== '' && !str_ends_with(trim($params), ',')) {
                        $params .= ',';
                    }

                    return "public function __construct(\n{$params}\n{$constructorParameter}\n    ) {";
                },
                $contents,
                1,
                $replacements
            );
        }

        if ($contents === null || $replacements !== 1) {
            throw new RuntimeException('Could not update AppController constructor.');
        }
    }

    $delegateMethod = <<<PHP
    public function {$methodName}(Request \$request, Response \$response): Response
    {
        return \$this->{$propertyName}->index(\$request, \$response);
    }
PHP;
    $eol = str_contains($contents, "\r\n") ? "\r\n" : "\n";
    $updatedContents = preg_replace(
        '/\R}\s*$/',
        "{$eol}{$eol}{$delegateMethod}{$eol}}{$eol}",
        $contents,
        1,
        $replacements
    );

    if ($updatedContents === null || $replacements !== 1) {
        throw new RuntimeException('Could not find AppController class closing brace.');
    }

    return $updatedContents;
}

function registerControllerRoute(string $routesPath, string $httpMethod, string $routePath, string $methodName): string
{
    if (!is_file($routesPath)) {
        writeGeneratedFile($routesPath, starterRoutesStub());
    }

    $contents = readFileOrFail($routesPath, 'routes file');

    $routeEntry = "        ['{$httpMethod}', '{$routePath}', [AppController::class, '{$methodName}']],";

    if (str_contains($contents, "['{$httpMethod}', '{$routePath}'") || str_contains($contents, "\$app->" . strtolower($httpMethod) . "('{$routePath}'")) {
        throw new RuntimeException("Route already exists: {$httpMethod} {$routePath}");
    }

    if (str_contains($contents, "[AppController::class, '{$methodName}']")) {
        throw new RuntimeException("Route handler already exists: AppController::{$methodName}");
    }

    $notFoundRoute = "        ['GET', '/404', [AppController::class, 'notFound']],";

    if (str_contains($contents, $notFoundRoute)) {
        return str_replace($notFoundRoute, "{$routeEntry}\n{$notFoundRoute}", $contents);
    }

    if (!str_contains($contents, 'use App\Controllers\AppController;')) {
        $contents = preg_replace(
            '/^use Framework\\\\App;/m',
            "use App\\Controllers\\AppController;\nuse Framework\\App;",
            $contents,
            1,
            $useReplacements
        );

        if ($contents === null || $useReplacements !== 1) {
            throw new RuntimeException('Could not find AppController route use insertion point.');
        }
    }

    $methodCall = strtolower($httpMethod);
    $directEntry = "    \$app->{$methodCall}('{$routePath}', [AppController::class, '{$methodName}']);";
    $updatedContents = preg_replace(
        '/\R}\s*$/',
        "\n{$directEntry}\n}\n",
        $contents,
        1,
        $routeReplacements
    );

    if ($updatedContents === null || $routeReplacements !== 1) {
        throw new RuntimeException('Could not find route registration insertion point.');
    }

    return $updatedContents;
}

function registerDirectControllerRoute(
    string $routesPath,
    string $folderName,
    string $controllerName,
    string $httpMethod,
    string $routePath
): string {
    $contents = file_get_contents($routesPath);

    if ($contents === false) {
        throw new RuntimeException("Could not read routes file: {$routesPath}");
    }

    $useStatement = "use App\\Controllers\\{$folderName}\\{$controllerName};";

    if (!str_contains($contents, $useStatement)) {
        $contents = preg_replace(
            '/^use Framework\\\\App;/m',
            "{$useStatement}\nuse Framework\\App;",
            $contents,
            1,
            $useReplacements
        );

        if ($contents === null || $useReplacements !== 1) {
            throw new RuntimeException('Could not find controller use insertion point.');
        }
    }

    $methodCall = strtolower($httpMethod);
    $routeEntry = "    \$app->{$methodCall}('{$routePath}', [{$controllerName}::class, 'index']);";

    if (str_contains($contents, "\$app->{$methodCall}('{$routePath}'")) {
        throw new RuntimeException("Route already exists: {$httpMethod} {$routePath}");
    }

    if (str_contains($contents, "[{$controllerName}::class, 'index']")) {
        throw new RuntimeException("Route handler already exists: {$controllerName}::index");
    }

    $updatedContents = preg_replace(
        '/\R}\s*$/',
        "\n{$routeEntry}\n}\n",
        $contents,
        1,
        $routeReplacements
    );

    if ($updatedContents === null || $routeReplacements !== 1) {
        throw new RuntimeException('Could not find route registration insertion point.');
    }

    return $updatedContents;
}

function readFileOrFail(string $path, string $label): string
{
    $contents = file_get_contents($path);

    if ($contents === false) {
        throw new RuntimeException("Could not read {$label}: {$path}");
    }

    return $contents;
}

function registerMiddlewareClass(string $middlewareConfigPath, string $folderName, string $middlewareName): string
{
    $contents = file_get_contents($middlewareConfigPath);

    if ($contents === false) {
        throw new RuntimeException("Could not read middleware config: {$middlewareConfigPath}");
    }

    $useStatement = "use App\\Middleware\\{$folderName}\\{$middlewareName};";
    $middlewareEntry = "    \$app->addMiddleware({$middlewareName}::class);";

    if (str_contains($contents, "use App\\Middleware\\{$middlewareName};") || str_contains($contents, $useStatement)) {
        throw new RuntimeException("Middleware import already exists for: {$middlewareName}");
    }

    if (str_contains($contents, "{$middlewareName}::class")) {
        throw new RuntimeException("Middleware is already registered: {$middlewareName}");
    }

    $contents = str_replace(
        "use Framework\\App;",
        "{$useStatement}\nuse Framework\\App;",
        $contents,
        $useReplacements
    );

    if ($useReplacements !== 1) {
        throw new RuntimeException('Could not find middleware use insertion point.');
    }

    $securityEntry = "    \$app->addMiddleware(SecurityHeadersMiddleware::class);";

    if (str_contains($contents, $securityEntry)) {
        return str_replace($securityEntry, "{$middlewareEntry}\n{$securityEntry}", $contents);
    }

    $updatedContents = preg_replace(
        '/\R}\s*$/',
        "\n{$middlewareEntry}\n}\n",
        $contents,
        1,
        $entryReplacements
    );

    if ($updatedContents === null || $entryReplacements !== 1) {
        throw new RuntimeException('Could not find middleware registration insertion point.');
    }

    return $updatedContents;
}

function registerControllerDefinition(string $containerDefinitionsPath, string $controllerNamespace, string $controllerName): string
{
    $contents = readFileOrFail($containerDefinitionsPath, 'container definitions');
    $qualifiedNamespace = trim($controllerNamespace, '\\');
    $useStatement = $qualifiedNamespace === ''
        ? "use App\\Controllers\\{$controllerName};"
        : "use App\\Controllers\\{$qualifiedNamespace}\\{$controllerName};";
    $definition = "    {$controllerName}::class => fn(\$container) => \$container->resolve({$controllerName}::class),";

    if (str_contains($contents, "{$controllerName}::class =>")) {
        throw new RuntimeException("Controller is already registered: {$controllerName}");
    }

    $contents = ensureUseStatement($contents, $useStatement, 'controller');

    $updatedContents = preg_replace(
        '/\R];\s*$/',
        "\n{$definition}\n];\n",
        $contents,
        1,
        $definitionReplacements
    );

    if ($updatedContents === null || $definitionReplacements !== 1) {
        throw new RuntimeException('Could not find container definition insertion point.');
    }

    return $updatedContents;
}

function registerServiceDefinition(string $containerDefinitionsPath, string $folderName, string $serviceName): string
{
    $contents = file_get_contents($containerDefinitionsPath);

    if ($contents === false) {
        throw new RuntimeException("Could not read container definitions: {$containerDefinitionsPath}");
    }

    $useStatement = "use App\\Services\\{$folderName}\\{$serviceName};";
    $definition = "    {$serviceName}::class => fn(\$container) => new {$serviceName}(fn() => \$container->get(Database::class)),";

    if (str_contains($contents, "use App\\Services\\{$serviceName};") || str_contains($contents, $useStatement)) {
        throw new RuntimeException("Service import already exists for: {$serviceName}");
    }

    if (str_contains($contents, "{$serviceName}::class =>")) {
        throw new RuntimeException("Service is already registered: {$serviceName}");
    }

    $contents = ensureUseStatement($contents, $useStatement, 'service');
    $contents = ensureUseStatement($contents, 'use Framework\Database;', 'database');

    $updatedContents = preg_replace(
        '/\R];\s*$/',
        "\n{$definition}\n];\n",
        $contents,
        1,
        $definitionReplacements
    );

    if ($updatedContents === null || $definitionReplacements !== 1) {
        throw new RuntimeException('Could not find container definition insertion point.');
    }

    return $updatedContents;
}

function ensureUseStatement(string $contents, string $useStatement, string $label): string
{
    if (str_contains($contents, $useStatement)) {
        return $contents;
    }

    $updatedContents = preg_replace(
        '/^use Framework\\\\/m',
        "{$useStatement}\nuse Framework\\",
        $contents,
        1,
        $useReplacements
    );

    if ($updatedContents === null || $useReplacements !== 1) {
        throw new RuntimeException("Could not find {$label} use insertion point.");
    }

    return $updatedContents;
}

function controllerStub(
    string $folderName,
    string $controllerName,
    string $viewPath,
    string $layoutPath,
    string $viewTitle,
    string $bodyClass
): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Controllers\\{$folderName};

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class {$controllerName}
{
    public function __construct(private TemplateEngine \$view)
    {
    }

    public function index(Request \$request, Response \$response): Response
    {
        return \$response->html(\$this->view->render('{$viewPath}', '{$layoutPath}', [
            'title' => '{$viewTitle}',
            'body_class' => '{$bodyClass}',
        ]));
    }
}

PHP;
}

function appControllerStub(): string
{
    return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Http\Request;
use Framework\Http\Response;

class AppController
{
    public function __construct(
    ) {
    }
}

PHP;
}

function middlewareStub(string $folderName, string $middlewareName): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Middleware\\{$folderName};

use Framework\Contracts\MiddlewareInterface;

class {$middlewareName} implements MiddlewareInterface
{
    public function process(callable \$next): mixed
    {
        // Add checks here. Return early to stop the request, or call \$next() to continue.
        return \$next();
    }
}

PHP;
}

function serviceStub(string $folderName, string $serviceName): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Services\\{$folderName};

use Closure;
use Framework\Database;

class {$serviceName}
{
    public function __construct(private Closure \$databaseFactory)
    {
    }

    public function handle(): void
    {
        // Put reusable business logic here.
    }
}

PHP;
}

function starterAppControllerStub(): string
{
    return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class AppController
{
    public function __construct(
        private WelcomeController $welcome,
        private TemplateEngine $view
    ) {
    }

    public function home(Request $request, Response $response): Response
    {
        return $this->welcome->index($request, $response);
    }

    public function notFound(Request $request, Response $response, ?string $message = null): Response
    {
        return $response->html($this->view->render('frontend/errors/not-found', 'layouts/frontend/paper', [
            'title' => 'Not Found',
            'body_class' => 'paper-not-found-page',
            'message' => $message ?? 'The page you requested could not be found.',
        ]), 404);
    }
}

PHP;
}

function welcomeControllerStub(): string
{
    return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class WelcomeController
{
    public function __construct(private TemplateEngine $view)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $response->html($this->view->render('frontend/welcome/index', 'layouts/frontend/paper', [
            'title' => 'Welcome',
            'body_class' => 'paper-welcome-page',
        ]));
    }
}

PHP;
}

function starterFrontendLayoutStub(): string
{
    return <<<'PHP'
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= escape($project ?? 'Paper-PHPFramework') ?>">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <title><?= escape($title ?? 'Welcome') ?> | <?= escape($project ?? 'Paper-PHPFramework') ?></title>
    <link rel="stylesheet" href="/assets/admin/css/app.css">
    <link rel="stylesheet" href="/assets/frontend/css/paper.css">
    <?= $this->section('styles') ?>
</head>

<body class="<?= escape($body_class ?? 'paper-frontend') ?>">
    <div id="loader" class="paper-loader" aria-hidden="true"></div>
    <div class="paper-front-shell">
        <nav class="paper-front-nav" aria-label="Main navigation">
            <a class="paper-front-brand" href="/">
                <span class="paper-brand-mark"><img src="/brand.png" alt=""></span>
                <span><?= escape($project ?? 'Paper-PHPFramework') ?></span>
            </a>
        </nav>

        <?= $this->section('content') ?>
    </div>

    <script src="/assets/frontend/js/app.js"></script>
    <?= $this->section('scripts') ?>
</body>

</html>
PHP;
}

function starterBackendLayoutStub(): string
{
    return <<<'PHP'
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <title><?= escape($title ?? 'Dashboard') ?> | <?= escape($project ?? 'Paper-PHPFramework') ?></title>
    <link rel="stylesheet" href="/assets/admin/css/app.css">
    <link rel="stylesheet" href="/assets/backend/css/paper.css">
    <?= $this->section('styles') ?>
</head>

<body class="<?= escape($body_class ?? 'paper-backend') ?>">
    <div id="loader" class="paper-loader" aria-hidden="true"></div>
    <div class="paper-back-layout">
        <aside class="paper-back-sidebar" aria-label="Backend navigation">
            <a class="paper-back-brand" href="/">
                <span class="paper-brand-mark"><img src="/brand.png" alt=""></span>
                <span><?= escape($project ?? 'Paper-PHPFramework') ?></span>
            </a>
            <nav class="paper-back-nav">
                <a class="active" href="/"><i class="icon-dashboard"></i><span>Dashboard</span></a>
            </nav>
        </aside>

        <main class="paper-back-main">
            <header class="paper-back-topbar">
                <div>
                    <strong><?= escape($title ?? 'Dashboard') ?></strong>
                    <span><?= escape(date('M d, Y')) ?></span>
                </div>
            </header>

            <section class="paper-back-content">
                <?= $this->section('content') ?>
            </section>
        </main>
    </div>

    <script src="/assets/backend/js/app.js"></script>
    <?= $this->section('scripts') ?>
</body>

</html>
PHP;
}

function starterWelcomeViewStub(): string
{
    return <<<'PHP'
<?= $this->start('content') ?>

<main class="paper-welcome">
    <section class="paper-welcome-copy">
        <div class="paper-kicker"><i class="icon-layers"></i> Starter Ready</div>
        <h1><?= escape($project ?? 'Paper-PHPFramework') ?></h1>
        <p>
            Your fresh project is using a Paper frontend layout, organized view folders,
            and separated frontend/backend assets.
        </p>
        <div class="paper-actions">
            <a class="paper-btn primary" href="/"><i class="icon-home"></i> Home</a>
        </div>
    </section>

    <section class="paper-preview" aria-label="Starter structure">
        <div class="paper-preview-head">
            <strong>Fresh Structure</strong>
            <span class="paper-pill">Ready</span>
        </div>
        <div class="paper-preview-list">
            <div><i class="icon-folder tone-blue"></i><span>views/layouts/frontend</span></div>
            <div><i class="icon-folder tone-green"></i><span>views/layouts/backend</span></div>
            <div><i class="icon-code tone-gold"></i><span>public/assets/frontend</span></div>
            <div><i class="icon-server tone-pink"></i><span>public/assets/backend</span></div>
        </div>
    </section>
</main>

<?= $this->end() ?>
PHP;
}

function starterNotFoundViewStub(): string
{
    return <<<'PHP'
<?= $this->start('content') ?>

<main class="paper-page">
    <section class="paper-page-header">
        <div class="paper-kicker"><i class="icon-alert-triangle"></i> 404</div>
        <h1><?= escape($title ?? 'Not Found') ?></h1>
        <p><?= escape($message ?? 'The page you requested could not be found.') ?></p>
        <div class="paper-actions">
            <a class="paper-btn primary" href="/"><i class="icon-home"></i> Home</a>
        </div>
    </section>

    <section class="paper-panel">
        <div class="paper-panel-head">
            <strong>Route Missing</strong>
            <span class="paper-pill">Handled</span>
        </div>
        <div class="paper-panel-body">
            <span>The starter route error handler is wired through <strong>AppController::notFound()</strong>.</span>
        </div>
    </section>
</main>

<?= $this->end() ?>
PHP;
}

function starterFrontendCssStub(): string
{
    return <<<'CSS'
:root {
    --paper-bg: #f7f8fb;
    --paper-surface: #ffffff;
    --paper-ink: #1d2733;
    --paper-muted: #667085;
    --paper-line: #dfe5ec;
    --paper-blue: #3277c8;
    --paper-green: #1f9d7a;
    --paper-gold: #b88b2d;
    --paper-pink: #cc4f82;
    --paper-shadow: 0 18px 48px rgba(29, 39, 51, .10);
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    color: var(--paper-ink);
    background: var(--paper-bg);
    letter-spacing: 0;
}

a {
    color: inherit;
    text-decoration: none;
}

#loader.paper-loader {
    display: none;
}

.paper-front-shell {
    min-height: 100vh;
    background:
        linear-gradient(90deg, rgba(50, 119, 200, .06) 1px, transparent 1px),
        linear-gradient(0deg, rgba(31, 157, 122, .05) 1px, transparent 1px),
        var(--paper-bg);
    background-size: 28px 28px;
}

.paper-front-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    width: min(1120px, calc(100% - 32px));
    margin: 0 auto;
    padding: 22px 0;
}

.paper-front-brand {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    color: var(--paper-ink);
    font-weight: 800;
}

.paper-brand-mark {
    display: inline-grid;
    place-items: center;
    width: 38px;
    height: 38px;
    border: 1px solid var(--paper-line);
    border-radius: 8px;
    background: var(--paper-surface);
    box-shadow: 0 10px 28px rgba(29, 39, 51, .08);
}

.paper-brand-mark img {
    max-width: 24px;
    max-height: 24px;
}

.paper-welcome,
.paper-page {
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(300px, .92fr);
    align-items: center;
    gap: 32px;
    width: min(1120px, calc(100% - 32px));
    min-height: calc(100vh - 96px);
    margin: 0 auto;
    padding: 28px 0 72px;
}

.paper-welcome-copy,
.paper-page-header {
    max-width: 660px;
}

.paper-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 18px;
    color: var(--paper-blue);
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
}

.paper-welcome h1,
.paper-page h1 {
    margin: 0;
    font-size: clamp(42px, 7vw, 82px);
    line-height: 1;
    letter-spacing: 0;
}

.paper-welcome p,
.paper-page p {
    max-width: 620px;
    margin: 22px 0 0;
    color: var(--paper-muted);
    font-size: 18px;
    line-height: 1.7;
}

.paper-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 28px;
}

.paper-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 42px;
    padding: 0 16px;
    border: 1px solid var(--paper-line);
    border-radius: 8px;
    background: var(--paper-surface);
    color: var(--paper-ink);
    font-weight: 700;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}

.paper-btn:hover {
    transform: translateY(-1px);
    border-color: rgba(50, 119, 200, .45);
    box-shadow: 0 10px 30px rgba(29, 39, 51, .10);
}

.paper-btn.primary {
    border-color: var(--paper-blue);
    background: var(--paper-blue);
    color: #fff;
}

.paper-preview,
.paper-panel {
    border: 1px solid var(--paper-line);
    border-radius: 8px;
    background: var(--paper-surface);
    box-shadow: var(--paper-shadow);
    overflow: hidden;
}

.paper-preview-head,
.paper-panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--paper-line);
}

.paper-preview-list {
    display: grid;
    gap: 12px;
    padding: 16px;
}

.paper-preview-list div,
.paper-panel-body {
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 58px;
    padding: 12px;
    border: 1px solid var(--paper-line);
    border-radius: 8px;
    background: #fbfcfe;
}

.paper-preview-list i {
    display: inline-grid;
    place-items: center;
    width: 38px;
    height: 38px;
    border-radius: 8px;
    color: #fff;
}

.paper-pill {
    display: inline-flex;
    align-items: center;
    min-height: 28px;
    padding: 0 10px;
    border-radius: 999px;
    background: rgba(31, 157, 122, .12);
    color: var(--paper-green);
    font-size: 12px;
    font-weight: 800;
    white-space: nowrap;
}

.tone-blue {
    background: var(--paper-blue);
}

.tone-green {
    background: var(--paper-green);
}

.tone-gold {
    background: var(--paper-gold);
}

.tone-pink {
    background: var(--paper-pink);
}

@media (max-width: 820px) {
    .paper-welcome,
    .paper-page {
        grid-template-columns: 1fr;
        min-height: auto;
        padding-top: 24px;
    }
}

@media (max-width: 620px) {
    .paper-front-nav {
        align-items: flex-start;
        flex-direction: column;
    }
}
CSS;
}

function starterFrontendJsStub(): string
{
    return <<<'JS'
document.documentElement.classList.add('paper-frontend-ready');
JS;
}

function starterBackendCssStub(): string
{
    return <<<'CSS'
:root {
    --paper-bg: #f7f8fb;
    --paper-surface: #ffffff;
    --paper-ink: #1d2733;
    --paper-muted: #667085;
    --paper-line: #dfe5ec;
    --paper-blue: #3277c8;
    --paper-green: #1f9d7a;
    --paper-gold: #b88b2d;
    --paper-pink: #cc4f82;
    --paper-shadow: 0 14px 34px rgba(29, 39, 51, .08);
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    color: var(--paper-ink);
    background: var(--paper-bg);
    letter-spacing: 0;
}

a {
    color: inherit;
    text-decoration: none;
}

#loader.paper-loader {
    display: none;
}

.paper-back-layout {
    min-height: 100vh;
    background: var(--paper-bg);
}

.paper-back-sidebar {
    position: fixed;
    inset: 0 auto 0 0;
    z-index: 20;
    width: 250px;
    border-right: 1px solid var(--paper-line);
    background: var(--paper-surface);
}

.paper-back-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 78px;
    padding: 0 20px;
    border-bottom: 1px solid var(--paper-line);
    color: var(--paper-ink);
    font-weight: 800;
}

.paper-brand-mark {
    display: inline-grid;
    place-items: center;
    width: 38px;
    height: 38px;
    border: 1px solid var(--paper-line);
    border-radius: 8px;
    background: var(--paper-surface);
    box-shadow: 0 10px 28px rgba(29, 39, 51, .08);
}

.paper-brand-mark img {
    max-width: 24px;
    max-height: 24px;
}

.paper-back-nav {
    display: grid;
    gap: 6px;
    padding: 16px 12px;
}

.paper-back-nav a {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 44px;
    padding: 0 12px;
    border-radius: 8px;
    color: var(--paper-muted);
    font-weight: 700;
}

.paper-back-nav a.active,
.paper-back-nav a:hover {
    background: rgba(50, 119, 200, .10);
    color: var(--paper-blue);
}

.paper-back-main {
    min-height: 100vh;
    margin-left: 250px;
}

.paper-back-topbar {
    position: sticky;
    top: 0;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    min-height: 72px;
    padding: 0 28px;
    border-bottom: 1px solid var(--paper-line);
    background: rgba(255, 255, 255, .92);
    backdrop-filter: blur(12px);
}

.paper-back-topbar strong,
.paper-back-topbar span {
    display: block;
}

.paper-back-topbar span {
    margin-top: 3px;
    color: var(--paper-muted);
    font-size: 13px;
}

.paper-back-content {
    width: min(1180px, 100%);
    padding: 28px;
}

.paper-panel {
    border: 1px solid var(--paper-line);
    border-radius: 8px;
    background: var(--paper-surface);
    box-shadow: var(--paper-shadow);
}

.paper-panel-head {
    padding: 18px 20px;
    border-bottom: 1px solid var(--paper-line);
    font-weight: 800;
}

.paper-panel-body {
    padding: 20px;
    color: var(--paper-muted);
    line-height: 1.7;
}

@media (max-width: 720px) {
    .paper-back-sidebar {
        position: static;
        width: auto;
    }

    .paper-back-main {
        margin-left: 0;
    }

    .paper-back-content,
    .paper-back-topbar {
        padding-right: 16px;
        padding-left: 16px;
    }
}
CSS;
}

function starterBackendJsStub(): string
{
    return <<<'JS'
document.documentElement.classList.add('paper-backend-ready');
JS;
}

function starterRoutesStub(): string
{
    return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Config;

use App\Controllers\AppController;
use Framework\App;

function registerRoutes(App $app): void
{
    $routes = [
        ['GET', '/', [AppController::class, 'home']],
        ['GET', '/404', [AppController::class, 'notFound']],
    ];

    foreach ($routes as $route) {
        [$method, $path, $handler, $middlewares] = array_pad($route, 4, []);
        $routeRegistration = $app->{strtolower($method)}($path, $handler);

        foreach ($middlewares as $middleware) {
            $routeRegistration->add($middleware);
        }
    }

    $app->setErrorHandler([AppController::class, 'notFound']);
}

PHP;
}

function starterContainerDefinitionsStub(): string
{
    return <<<'PHP'
<?php

declare(strict_types=1);

use App\Config\Paths;
use App\Controllers\AppController;
use App\Controllers\WelcomeController;
use Framework\Database;
use Framework\TemplateEngine;

return [
    TemplateEngine::class => fn() => new TemplateEngine(Paths::VIEW),
    Database::class => fn() => new Database(
        (string) ($_ENV['DB_DRIVER'] ?? 'mysql'),
        [
            'host' => (string) ($_ENV['DB_HOST'] ?? 'localhost'),
            'port' => (string) ($_ENV['DB_PORT'] ?? '3306'),
            'dbname' => (string) ($_ENV['DB_NAME'] ?? 'paper_phpframework'),
        ],
        (string) ($_ENV['DB_USER'] ?? 'root'),
        (string) ($_ENV['DB_PASSWORD'] ?? '')
    ),
    AppController::class => fn($container) => $container->resolve(AppController::class),
    WelcomeController::class => fn($container) => $container->resolve(WelcomeController::class),
];

PHP;
}

function viewStub(string $viewTitle): string
{
    return <<<PHP
<?= \$this->start('content') ?>

<main class="paper-page">
    <section class="paper-page-header">
        <div class="paper-kicker"><i class="icon-layers"></i> Generated Page</div>
        <h1><?= escape(\$title ?? '{$viewTitle}') ?></h1>
        <p>This page was generated by the Paper-PHPFramework controller CLI.</p>
        <div class="paper-actions">
            <a class="paper-btn primary" href="/"><i class="icon-home"></i> Home</a>
        </div>
    </section>

    <section class="paper-panel">
        <div class="paper-panel-head">
            <strong>{$viewTitle}</strong>
            <span class="paper-pill">Ready</span>
        </div>
        <div class="paper-panel-body">
            <span>Build this page from <strong>{$viewTitle}</strong>.</span>
        </div>
    </section>
</main>

<?= \$this->end() ?>

PHP;
}

function hydrateImages(string $root, array $arguments): void
{
    require_once $root . '/src/App/functions.php';

    $options = cliImageCommandOptions($arguments);
    $db = cliDatabase($root);
    $importer = new App\Services\TMDB\TmdbImporterService($db);

    $iterations = 0;
    do {
        $iterations++;
        $result = $importer->hydrateMissingImages($options['scope'], $options['limit']);
        echo sprintf(
            "Hydrate batch %d: scanned=%d hydrated=%d posters=%d backdrops=%d failed=%d\n",
            $iterations,
            (int) ($result['scanned'] ?? 0),
            (int) ($result['hydrated'] ?? 0),
            (int) ($result['posters_hydrated'] ?? 0),
            (int) ($result['backdrops_hydrated'] ?? 0),
            (int) ($result['failed'] ?? 0)
        );
    } while ($options['loop'] && (int) ($result['hydrated'] ?? 0) > 0 && $iterations < $options['max_iterations']);
}

function regenerateImageVariants(string $root, array $arguments): void
{
    require_once $root . '/src/App/functions.php';

    $options = cliImageCommandOptions($arguments);
    $db = cliDatabase($root);
    $importer = new App\Services\TMDB\TmdbImporterService($db);

    $iterations = 0;
    do {
        $iterations++;
        $result = $importer->regenerateImageVariants($options['scope'], $options['limit']);
        echo sprintf(
            "Regenerate batch %d: scanned=%d regenerated=%d failed=%d\n",
            $iterations,
            (int) ($result['scanned'] ?? 0),
            (int) ($result['variants_regenerated'] ?? 0),
            (int) ($result['failed'] ?? 0)
        );
    } while ($options['loop'] && (int) ($result['variants_regenerated'] ?? 0) > 0 && $iterations < $options['max_iterations']);
}

/**
 * @return array{scope: string, limit: int, loop: bool, max_iterations: int}
 */
function cliImageCommandOptions(array $arguments): array
{
    $scope = 'all';
    $limit = 50;
    $loop = false;
    $maxIterations = 200;

    foreach ($arguments as $argument) {
        if (str_starts_with($argument, '--scope=')) {
            $scope = substr($argument, 8);
        } elseif (str_starts_with($argument, '--limit=')) {
            $limit = max(1, (int) substr($argument, 8));
        } elseif ($argument === '--loop') {
            $loop = true;
        } elseif (str_starts_with($argument, '--max-iterations=')) {
            $maxIterations = max(1, (int) substr($argument, 17));
        }
    }

    return [
        'scope' => $scope,
        'limit' => $limit,
        'loop' => $loop,
        'max_iterations' => $maxIterations,
    ];
}

function cliDatabase(string $root): Framework\Database
{
    return new Framework\Database(
        envValue('DB_DRIVER', 'mysql'),
        [
            'host'   => envValue('DB_HOST', 'localhost'),
            'port'   => envValue('DB_PORT', '3306'),
            'dbname' => envValue('DB_NAME', 'paper_phpframework'),
        ],
        envValue('DB_USER', 'root'),
        envValue('DB_PASSWORD', '')
    );
}

function publishScheduledEpisodes(string $root): void
{
    // Bootstrap just enough to get a Database + TmdbImporterService instance.
    require_once $root . '/src/App/functions.php';

    $db = new Framework\Database(
        envValue('DB_DRIVER', 'mysql'),
        [
            'host'   => envValue('DB_HOST', 'localhost'),
            'port'   => envValue('DB_PORT', '3306'),
            'dbname' => envValue('DB_NAME', 'paper_phpframework'),
        ],
        envValue('DB_USER', 'root'),
        envValue('DB_PASSWORD', '')
    );

    $importer = new App\Services\TMDB\TmdbImporterService($db);
    $result   = $importer->publishScheduled();

    $total = $result['published'];
    echo "Published {$total} episode(s) with fresh TMDB metadata.\n";

    if (!empty($result['errors'])) {
        echo "Errors (" . count($result['errors']) . "):\n";
        foreach ($result['errors'] as $error) {
            echo "  - {$error}\n";
        }
    }
}

function installDatabase(string $root): void
{
$driver = envValue('DB_DRIVER', 'mysql');
$host = envValue('DB_HOST', 'localhost');
$port = envValue('DB_PORT', '3306');
$database = envValue('DB_NAME', 'paper_phpframework');
$username = envValue('DB_USER', 'root');
$password = envValue('DB_PASSWORD', '');
$sqlPath = $root . '/database.sql';

if ($driver !== 'mysql') {
throw new RuntimeException('Only mysql is supported by this starter installer.');
}

if (!is_file($sqlPath)) {
throw new RuntimeException('Missing database.sql in the project root.');
}

$databaseName = str_replace('`', '``', $database);
$dsn = "mysql:host={$host};port={$port};charset=utf8mb4";

try {
$pdo = new PDO($dsn, $username, $password, [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$databaseName}`");
$pdo->exec((string) file_get_contents($sqlPath));

echo "Database imported successfully.\n";
echo "Database: {$database}\n";
} catch (Throwable $exception) {
throw new RuntimeException("Database import failed: {$exception->getMessage()}", 0, $exception);
}
}

function envValue(string $key, string $default = ''): string
{
return (string) ($_ENV[$key] ?? getenv($key) ?: $default);
}
