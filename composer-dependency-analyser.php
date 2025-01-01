<?php

/**
 * Dependency analyzer configuration
 * @link https://github.com/shipmonk-rnd/composer-dependency-analyser
 */

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$sourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'php-src' . DIRECTORY_SEPARATOR;
$testPath = __DIR__ . DIRECTORY_SEPARATOR . 'php-tests' . DIRECTORY_SEPARATOR;

$config = new Configuration();

return $config
    // ignore errors on specific packages and paths
    ->addPathToScan($sourcePath, false)
    ->addPathToScan($testPath . 'CommonTestClass.php', true)
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_paths', $sourcePath . 'Mappers' . DIRECTORY_SEPARATOR . 'File' . DIRECTORY_SEPARATOR . 'AFile.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_paths', $sourcePath . 'Mappers' . DIRECTORY_SEPARATOR . 'File' . DIRECTORY_SEPARATOR . 'AFileSource.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_files', $sourcePath . 'Mappers' . DIRECTORY_SEPARATOR . 'File' . DIRECTORY_SEPARATOR . 'AFile.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_files', $sourcePath . 'Mappers' . DIRECTORY_SEPARATOR . 'File' . DIRECTORY_SEPARATOR . 'AFileSource.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_files', $sourcePath . 'Storage' . DIRECTORY_SEPARATOR . 'File' . DIRECTORY_SEPARATOR . 'FilesSingleton.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_files', $sourcePath . 'Storage' . DIRECTORY_SEPARATOR . 'File' . DIRECTORY_SEPARATOR . 'TFileAccessors.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_storage', $sourcePath . 'Mappers' . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'AFile.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_storage', $sourcePath . 'Mappers' . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'AStorage.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_storage', $sourcePath . 'Mappers' . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'KeyValue.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_storage', $sourcePath . 'Storage' . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'StorageSingleton.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('alex-kalanis/kw_storage', $sourcePath . 'Storage' . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'TStorage.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnExtensionAndPath('ext-pdo', $sourcePath . 'Storage' . DIRECTORY_SEPARATOR . 'Database', [ErrorType::DEV_DEPENDENCY_IN_PROD]) // this is because this package is also for non-standard-db systems with storages like files
    ->ignoreErrorsOnExtensionAndPath('ext-dba', $sourcePath . 'Storage' . DIRECTORY_SEPARATOR . 'Database', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnExtensionAndPath('ext-ldap', $sourcePath . 'Storage' . DIRECTORY_SEPARATOR . 'Database', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnExtensionAndPath('ext-mongodb', $sourcePath . 'Storage' . DIRECTORY_SEPARATOR . 'Database', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnExtensionAndPath('ext-mysqli', $sourcePath . 'Storage' . DIRECTORY_SEPARATOR . 'Database', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnExtensionAndPath('ext-odbc', $sourcePath . 'Storage' . DIRECTORY_SEPARATOR . 'Database', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnExtensionAndPath('ext-yaml', $sourcePath . 'Storage' . DIRECTORY_SEPARATOR . 'Database', [ErrorType::SHADOW_DEPENDENCY])
;
