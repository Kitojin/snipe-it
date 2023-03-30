<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Setting;
use Artisan;
use DB;
use Illuminate\Console\Command;

class RandomiseAssetTags extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'snipeit:randomise-tags {--output= : info|warn|error|all} ';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'This utility will regenerate all asset tags randomly. THIS IS DATA-DESTRUCTIVE AND SHOULD BE USED WITH CAUTION. ';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		if ($this->confirm('This will regenerate all of the asset tags within your system randomly. This action is data-destructive and should be used with caution. Do you wish to continue?')) {
			$output['info'] = [];
			$output['warn'] = [];
			$output['error'] = [];
			$settings = Setting::getSettings();

			$charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
			$attempts = 100;

			//should return an associative array of [modelID=>prefix]
			$CategoryPrefixes = Category::join('models', 'models.category_id', '=', 'categories.id')->pluck('category_prefix', 'models.id');
			// $output['info'][] = 'Category Prefixes:'.$CategoryPrefixes;
			// $output['info'][] = 'Prefix for ID 1:'.$CategoryPrefixes[1];

			$total_assets = Asset::orderBy('id', 'asc')->get();
			$bar = $this->output->createProgressBar(count($total_assets));

			try {
				Artisan::call('backup:run');
			} catch (\Exception $e) {
				$output['error'][] = $e;
			}

			foreach ($total_assets as $asset) {

				$output['info'][] = 'Asset tag:' . $asset->asset_tag;
				$asset->name = $asset->asset_tag;

				$nextRandomTag = null;
				$foundTag = false;
				while (!$foundTag) {
					$randomTags = array();
					for ($i = 0; $i < $attempts; $i++) {
						$currentTag = '';
						//Zerofill count is a bit of a misnomer here. It's the setting to describe how long a generated tag should be. 
						for ($j = 0; $j < $settings->zerofill_count; $j++) {
							$currentTag .= $charset[random_int(0, strlen($charset) - 1)];
						}
						$randomTags[] = $settings->auto_increment_prefix . $CategoryPrefixes[$asset->model_id] . $currentTag;
					}
					$invalidTags = DB::table('assets')->whereIn('asset_tag', $randomTags)->pluck('asset_tag')->toArray();
					$validTags = array_diff($randomTags, $invalidTags);

					if (count($validTags) > 0) {
						$foundTag = true;
						$nextRandomTag = $validTags[0];
					}
				}

				$asset->asset_tag = $nextRandomTag;

				$output['info'][] = 'New Asset tag: ' . $asset->asset_tag;
				$output['info'][] = 'Old Asset tag -> Asset Name: ' . $asset->name;
				$output['info'][] = PHP_EOL;

				// Use forceSave here to override model level validation
				$asset->forceSave();
				if ($bar) {
					$bar->advance();
				}
			}

			$bar->finish();
			$this->info("\n");

			if (($this->option('output') == 'all') || ($this->option('output') == 'info')) {
				foreach ($output['info'] as $key => $output_text) {
					$this->info($output_text);
				}
			}
			if (($this->option('output') == 'all') || ($this->option('output') == 'warn')) {
				foreach ($output['warn'] as $key => $output_text) {
					$this->warn($output_text);
				}
			}
			if (($this->option('output') == 'all') || ($this->option('output') == 'error')) {
				foreach ($output['error'] as $key => $output_text) {
					$this->error($output_text);
				}
			}
		}
	}
}