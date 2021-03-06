<?php

namespace RachidLaasri\LaravelInstaller\Helpers;

use Exception;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\BufferedOutput;
use Hash, Session;

class DatabaseUpdateManager
{
    /**
     * Migrate and seed the database.
     *
     * @return array
     */
    public function migrateAndSeedUpdate()
    {
        $outputLog = new BufferedOutput;

        $this->sqlite($outputLog);

        return $this->migrateUpdate($outputLog);
    }

    /**
     * Run the migration and call the seeder.
     *
     * @param collection $outputLog
     * @return collection
     */
    private function migrateUpdate($outputLog)
    {
        try{
            Artisan::call('migrate', ['--path' => 'vendor/laravel/passport/database/migrations'], $outputLog);
            Artisan::call('migrate', ["--force"=> true], $outputLog);
        }
        catch(Exception $e){
            return $this->response($e->getMessage(), 'error', $outputLog);
        }

        return $this->seedUpdate($outputLog);
    }

    /**
     * Seed the database.
     *
     * @param collection $outputLog
     * @return array
     */
    private function seedUpdate($outputLog)
    {

        try{
            Artisan::call('db:seed', [  ], $outputLog);
        }
        catch(Exception $e){
            return $this->response($e->getMessage(), 'error', $outputLog);
        }

        return $this->response(trans('installer_messages.final.finished'), 'success', $outputLog);
    }

    /**
     * Return a formatted error messages.
     *
     * @param $message
     * @param string $status
     * @param collection $outputLog
     * @return array
     */
    private function response($message, $status = 'danger', $outputLog)
    {
        return [
            'status' => $status,
            'message' => $message,
            'dbOutputLog' => $outputLog->fetch()
        ];
    }

    /**
     * check database type. If SQLite, then create the database file.
     *
     * @param collection $outputLog
     */
    private function sqlite($outputLog)
    {
        if(DB::connection() instanceof SQLiteConnection) {
            $database = DB::connection()->getDatabaseName();
            if(!file_exists($database)) {
                touch($database);
                DB::reconnect(Config::get('database.default'));
            }
            $outputLog->write('Using SqlLite database: ' . $database, 1);
        }
    }
}
