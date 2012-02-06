<?php namespace Laravel\Docs;

class API {

	/**
	 * The Github API instance.
	 *
	 * @var Github_Client
	 */
	protected $github;

	/**
	 * The cache driver instance.
	 *
	 * @var Laravel\Cache\Drivers\Driver
	 */
	protected $cache;

	/**
	 * Create a new Laravel documentation API instance.
	 *
	 * @param  Github_Client
	 * @param  Laravel\Cache\Drivers\Driver
	 * @return void
	 */
	public function __construct($github, $cache)
	{
		$this->cache = $cache;
		$this->github = $github;
	}

	/**
	 * Get a page from the documentation.
	 *
	 * @param  string  $section
	 * @param  string  $page
	 * @param  string  $branch
	 * @return string
	 */
	public function page($page, $section = null, $branch = 'master')
	{
		$page = $page.'.md';

		// If the page is cached, we'll just return it out of the cache.
		if ( ! is_null($cache = $this->cached($section, $page, $branch)))
		{
			return $cache;
		}

		// Grab the SHA identifier for the requested branch.
		$sha = $this->branch_sha($branch);

		// Grab all of the tree for the given branch.
		$tree = $this->github->getObjectApi()->showTree('laravel', 'docs', $sha);

		// If no section was specified, just load a file from the root of the tree.
		if (is_null($section))
		{
			$markdown = $this->page_from_tree($page, $tree);
		}
		// If a section was specified, drill into the section and get the page.
		else
		{
			$markdown = $this->from_section($page, $section, $tree);
		}

		$this->cache($section, $page, $branch, $markdown);

		return $markdown;
	}

	/**
	 * Load a page from a given section.
	 *
	 * @param  string  $page
	 * @param  string  $section
	 * @param  array   $tree
	 * @return string
	 */
	protected function from_section($page, $section, $tree)
	{
		$sha = $this->section_sha($section, $tree);

		$tree = $this->github->getObjectApi()->showTree('laravel', 'docs', $sha);

		return $this->page_from_tree($page, $tree);	
	}

	/**
	 * Get the Markdown of a page from a given tree.
	 *
	 * @param  string  $page
	 * @param  string  $tree
	 * @return string
	 */
	protected function page_from_tree($page, $tree)
	{
		$array = array_first($tree, function($key, $value) use ($page)
		{
			return $value['name'] == $page and $value['type'] == 'blob';
		});

		if ( ! is_null($array))
		{
			return $this->github->getObjectApi()->getRawData('laravel', 'docs', $array['sha']);
		}
	}

	/**
	 * Get the SHA for a given section in a tree.
	 *
	 * @param  string  $section
	 * @param  string  $tree
	 * @return string
	 */
	protected function section_sha($section, $tree)
	{
		$array = array_first($tree, function($key, $value) use ($section)
		{
			return $value['name'] == $section and $value['type'] == 'tree';
		});

		return $array['sha'];
	}

	/**
	 * Store a page in the cache.
	 *
	 * @param  string  $section
	 * @param  string  $page
	 * @param  string  $branch
	 * @param  string  $markdown
	 * @return void
	 */
	protected function cache($section, $page, $branch, $markdown)
	{
		$this->cache->put($this->cache_key($section, $page, $branch), $markdown, 60);
	}

	/**
	 * Attempt to retrieve a cached page.
	 *
	 * @param  string  $section
	 * @param  string  $page
	 * @param  string  $branch
	 * @return string
	 */
	protected function cached($section, $page, $branch)
	{
		return $this->cache->get($this->cache_key($section, $page, $branch));
	}

	/**
	 * Get the cache key for given page.
	 *
	 * @param  string  $section
	 * @param  string  $page
	 * @param  string  $branch
	 * @return string
	 */
	protected function cache_key($section, $page, $branch)
	{
		if (is_null($section)) $section = 'root';

		$page = str_replace('.md', '', $page);

		return "laravel.docs.{$section}.{$page}.{$branch}";
	}

	/**
	 * Get the SHA for a given branch.
	 *
	 * @param  string  $branch
	 * @return string
	 */
	protected function branch_sha($branch)
	{
		$branches = $this->github->getRepoApi()->getRepoBranches('laravel', 'docs');

		return array_get($branches, $branch);
	}

}