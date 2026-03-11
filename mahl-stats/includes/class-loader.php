<?php
/**
 * Loader class for registering WordPress hooks.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Loader
{
	/**
	 * Registered actions.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	protected array $actions = [];

	/**
	 * Registered filters.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	protected array $filters = [];

	/**
	 * Add action hook.
	 *
	 * @param string $hook Hook name.
	 * @param object $component Class instance.
	 * @param string $callback Callback method.
	 * @param int    $priority Priority.
	 * @param int    $accepted_args Accepted args.
	 * @return void
	 */
	public function add_action(
		string $hook,
		object $component,
		string $callback,
		int $priority = 10,
		int $accepted_args = 1
	): void {
		$this->actions[] = [
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		];
	}

	/**
	 * Add filter hook.
	 *
	 * @param string $hook Hook name.
	 * @param object $component Class instance.
	 * @param string $callback Callback method.
	 * @param int    $priority Priority.
	 * @param int    $accepted_args Accepted args.
	 * @return void
	 */
	public function add_filter(
		string $hook,
		object $component,
		string $callback,
		int $priority = 10,
		int $accepted_args = 1
	): void {
		$this->filters[] = [
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		];
	}

	/**
	 * Register all hooks with WordPress.
	 *
	 * @return void
	 */
	public function run(): void
	{
		foreach ($this->filters as $hook) {
			add_filter(
				$hook['hook'],
				[$hook['component'], $hook['callback']],
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ($this->actions as $hook) {
			add_action(
				$hook['hook'],
				[$hook['component'], $hook['callback']],
				$hook['priority'],
				$hook['accepted_args']
			);
		}
	}
}