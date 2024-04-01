<?php

/**
 * Dependency analyzer configuration
 * @link https://github.com/shipmonk-rnd/composer-dependency-analyser
 */

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = new Configuration();

return $config
    // ignore errors on specific packages and paths
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_paths', __DIR__ . '/php-src/Mappers/File/AFile.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_paths', __DIR__ . '/php-src/Mappers/File/AFileSource.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_files', __DIR__ . '/php-src/Mappers/File/AFile.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_files', __DIR__ . '/php-src/Mappers/File/AFileSource.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_files', __DIR__ . '/php-src/Storage/File/FilesSingleton.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_files', __DIR__ . '/php-src/Storage/File/TFileAccessors.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_storage', __DIR__ . '/php-src/Mappers/Storage/AFile.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_storage', __DIR__ . '/php-src/Mappers/Storage/AStorage.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_storage', __DIR__ . '/php-src/Mappers/Storage/KeyValue.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_storage', __DIR__ . '/php-src/Storage/Storage/StorageSingleton.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_storage', __DIR__ . '/php-src/Storage/Storage/TStorage.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('COM', __DIR__ . '/php-src/Storage/Shared/DotNet/ComRegistry.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
;
