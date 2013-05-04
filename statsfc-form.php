<?php
/*
Plugin Name: StatsFC Form
Plugin URI: https://statsfc.com/developers
Description: StatsFC Form Guide
Version: 1.0.7
Author: Will Woodward
Author URI: http://willjw.co.uk
License: GPL2
*/

/*  Copyright 2013  Will Woodward  (email : will@willjw.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('STATSFC_FORM_ID',	'StatsFC_Form');
define('STATSFC_FORM_NAME',	'StatsFC Form');

/**
 * Adds StatsFC widget.
 */
class StatsFC_Form extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(STATSFC_FORM_ID, STATSFC_FORM_NAME, array('description' => 'StatsFC League Table'));
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form($instance) {
		$defaults = array(
			'title'			=> __('Form Guide', STATSFC_FORM_ID),
			'api_key'		=> __('', STATSFC_FORM_ID),
			'team'			=> __('', STATSFC_FORM_ID),
			'highlight'		=> __('', STATSFC_FORM_ID),
			'default_css'	=> __('', STATSFC_FORM_ID)
		);

		$instance		= wp_parse_args((array) $instance, $defaults);
		$title			= strip_tags($instance['title']);
		$api_key		= strip_tags($instance['api_key']);
		$team			= strip_tags($instance['team']);
		$highlight		= strip_tags($instance['highlight']);
		$default_css	= strip_tags($instance['default_css']);

		$teams = $this->_teamsFromAPI($api_key);
		?>
		<p>
			<label>
				<?php _e('Title', STATSFC_FORM_ID); ?>:
				<input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
			</label>
		</p>
		<p>
			<label>
				<?php _e('API key', STATSFC_FORM_ID); ?>:
				<input class="widefat" name="<?php echo $this->get_field_name('api_key'); ?>" type="text" value="<?php echo esc_attr($api_key); ?>">
			</label>
		</p>
		<p>
			<label>
				<?php _e('Team', STATSFC_FORM_ID); ?>:
				<?php
				if ($teams !== false) {
				?>
					<select class="widefat" name="<?php echo $this->get_field_name('team'); ?>">
						<option></option>
						<?php
						foreach ($teams as $row) {
							echo '<option value="' . esc_attr($row->path) . '"' . ($row->path == $team ? ' selected' : '') . '>' . esc_attr($row->name) . '</option>' . PHP_EOL;
						}
						?>
					</select>
				<?php
				} else {
				?>
					<input class="widefat" name="<?php echo $this->get_field_name('team'); ?>" type="text" value="<?php echo esc_attr($team); ?>">
				<?php
				}
				?>
			</label>
		</p>
		<p>
			<label>
				<?php _e('Highlight', STATSFC_FORM_ID); ?>:
				<?php
				if ($teams !== false) {
				?>
					<select class="widefat" name="<?php echo $this->get_field_name('highlight'); ?>">
						<option></option>
						<?php
						foreach ($teams as $row) {
							echo '<option value="' . esc_attr($row->name) . '"' . ($row->name == $highlight ? ' selected' : '') . '>' . esc_attr($row->name) . '</option>' . PHP_EOL;
						}
						?>
					</select>
				<?php
				} else {
				?>
					<input class="widefat" name="<?php echo $this->get_field_name('highlight'); ?>" type="text" value="<?php echo esc_attr($highlight); ?>">
				<?php
				}
				?>
			</label>
		</p>
		<p>
			<label>
				<?php _e('Use default CSS?', STATSFC_FORM_ID); ?>
				<input type="checkbox" name="<?php echo $this->get_field_name('default_css'); ?>"<?php echo ($default_css == 'on' ? ' checked' : ''); ?>>
			</label>
		</p>
	<?php
	}

	private function _teamsFromAPI($key) {
		$data = file_get_contents('https://api.statsfc.com/premier-league/teams.json?key=' . (! empty($key) ? $key : 'free'));

		if (empty($data)) {
			return false;
		}

		$json = json_decode($data);
		if (isset($json->error)) {
			return false;
		}

		return $json;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance) {
		$instance					= $old_instance;
		$instance['title']			= strip_tags($new_instance['title']);
		$instance['api_key']		= strip_tags($new_instance['api_key']);
		$instance['team']			= strip_tags($new_instance['team']);
		$instance['highlight']		= strip_tags($new_instance['highlight']);
		$instance['default_css']	= strip_tags($new_instance['default_css']);

		return $instance;
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget($args, $instance) {
		extract($args);

		$title			= apply_filters('widget_title', $instance['title']);
		$api_key		= $instance['api_key'];
		$team			= $instance['team'];
		$highlight		= $instance['highlight'];
		$default_css	= $instance['default_css'];

		echo $before_widget;
		echo $before_title . $title . $after_title;

		$data = file_get_contents('https://api.statsfc.com/premier-league/form.json?key=' . $api_key);

		try {
			if (empty($data)) {
				throw new Exception('There was an error connecting to the StatsFC API');
			}

			$json = json_decode($data);
			if (isset($json->error)) {
				throw new Exception($json->error);
			}

			if ($default_css) {
				wp_register_style(STATSFC_FORM_ID . '-css', plugins_url('all.css', __FILE__));
				wp_enqueue_style(STATSFC_FORM_ID . '-css');
			}
			?>
			<div class="statsfc_form">
				<table>
					<?php
					if (empty($team)) {
					?>
						<thead>
							<tr>
								<th></th>
								<th>Team</th>
								<th colspan="6" class="statsfc_numeric">Last 6 Results</th>
							</tr>
						</thead>
					<?php
					}
					?>
					<tbody>
						<?php
						foreach ($json as $row) {
							if (! empty($team) && $team !== $row->team) {
								continue;
							}

							$classes = array();

							if (! empty($highlight) && $highlight == $row->team) {
								$classes[] = 'statsfc_highlight';
							}
							?>
							<tr<?php echo (! empty($classes) ? ' class="' . implode(' ', $classes) . '"' : ''); ?>>
								<?php
								if (empty($team)) {
								?>
									<td class="statsfc_numeric"><?php echo esc_attr($row->position); ?></td>
								<?php
								}
								?>
								<td class="statsfc_team statsfc_badge_<?php echo str_replace(' ', '', strtolower($row->team)); ?>"><?php echo esc_attr($row->teamshort); ?></td>
								<?php
								foreach ($row->form as $result) {
								?>
									<td class="statsfc_form_results"><span class="statsfc_<?php echo strtolower($result); ?>"><?php echo esc_attr($result); ?></span></td>
								<?php
								}
								?>
							</tr>
						<?php
						}
						?>
					</tbody>
				</table>

				<p class="statsfc_footer"><small>Powered by StatsFC.com</small></p>
			</div>
		<?php
		} catch (Exception $e) {
			echo '<p class="statsfc_error">' . esc_attr($e->getMessage()) .'</p>' . PHP_EOL;
		}

		echo $after_widget;
	}
}

// register StatsFC widget
add_action('widgets_init', create_function('', 'register_widget("' . STATSFC_FORM_ID . '");'));
?>