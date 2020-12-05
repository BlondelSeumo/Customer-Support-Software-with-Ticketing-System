<?php

namespace Common\Database;

use Common\Core\Manifest\BuildManifestFile;
use DatabaseSeeder;
use File;
use Illuminate\Database\Eloquent\Model;

class MigrateAndSeed
{
    /**
     * @param Callable $afterMigrateCallback
     */
    public function execute($afterMigrateCallback = null)
    {
        // Migrate
        if ( ! app('migrator')->repositoryExists()) {
            app('migration.repository')->createRepository();
        }
        $migrator = app('migrator');
        $paths = $migrator->paths();
        $paths[] = app('path.database').DIRECTORY_SEPARATOR.'migrations';
        $migrator->run($paths);

        $afterMigrateCallback && $afterMigrateCallback();

        // Common seed
        $paths = File::files(app('path.common').'/Database/Seeds');
        foreach ($paths as $fileInfo) {
            Model::unguarded(function() use($fileInfo) {
                $namespace = 'Common\Database\Seeds\\'.$fileInfo->getBaseName('.php');
                $seeder = app($namespace)->setContainer(app());
                $seeder->__invoke();
            });
        }

        // Seed
        $seeder = app(DatabaseSeeder::class);
        $seeder->setContainer(app());
        Model::unguarded(function() use($seeder) {
            $seeder->__invoke();
        });

        // Manifest
        app(BuildManifestFile::class)->execute();
    }

}
