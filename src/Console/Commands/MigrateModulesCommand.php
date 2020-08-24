<?php

namespace Lararole\Console\Commands;

use Lararole\Models\Role;
use Lararole\Models\Module;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateModulesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:modules {--sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert all modules to database or Sync all modules';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $i=1;
            if (! $this->option('sync')) {
                foreach (config('lararole.modules') as $module) {
                    $m = Module::create([
                        'name' => $module['name'],
                        'icon' => @$module['icon'],
                        'alias' => @$module['alias'] ?? $module['name'],
                        'sequence' => $i++,
                    ]);
                    if (@$module['modules']) {
                        $i = $m->createModules(@$module['modules'], $i++);
                    }
                }

                $this->info('All modules and sub modules migrated successful!');
            } else {
                foreach (config('lararole.modules') as $module) {

                    $m = Module::updateOrCreate([
                        'name' => $module['name'],
                    ], [
                        'icon' => @$module['icon'],
                        'alias' => @$module['alias'] ?? $module['name'],
                        'sequence' => $i++,
                    ]);

                    if (@$module['modules']) {
                        $i = $m->updateOrCreateModules(@$module['modules'], $i++);
                    }
                }

                $this->info('All modules synced!');

                $super_admin_role = Role::whereSlug('super-admin')->first();
                if ($super_admin_role) {
                    $super_admin_role->modules()->detach();
                    $super_admin_role->modules()->attach(config('lararole.attachAllChildren') ? Module::root()->get() : Module::all(), ['permission' => 'write']);
                }
            }
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
        }
    }
}
