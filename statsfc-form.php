<?php
/*
Plugin Name: StatsFC Form
Plugin URI: https://statsfc.com/developers
Description: StatsFC Form Guide
Version: 1.0
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

define('FORM_ID',	'StatsFC_Form');
define('FORM_NAME',	'StatsFC Form');

/**
 * Adds StatsFC widget.
 */
class StatsFC_Form extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(FORM_ID, FORM_NAME, array('description' => 'StatsFC League Table'));
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
			'title'			=> __('Form Guide', FORM_ID),
			'api_key'		=> __('', FORM_ID),
			'highlight'		=> __('', FORM_ID),
			'default_css'	=> __('', FORM_ID)
		);

		$instance		= wp_parse_args((array) $instance, $defaults);
		$title			= strip_tags($instance['title']);
		$api_key		= strip_tags($instance['api_key']);
		$highlight		= strip_tags($instance['highlight']);
		$default_css	= strip_tags($instance['default_css']);
		?>
		<p>
			<label>
				<?php _e('Title', FORM_ID); ?>:
				<input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
			</label>
		</p>
		<p>
			<label>
				<?php _e('API key', FORM_ID); ?>:
				<input class="widefat" name="<?php echo $this->get_field_name('api_key'); ?>" type="text" value="<?php echo esc_attr($api_key); ?>">
			</label>
		</p>
		<p>
			<label>
				<?php _e('Highlight', FORM_ID); ?>:
				<input class="widefat" name="<?php echo $this->get_field_name('highlight'); ?>" type="text" value="<?php echo esc_attr($highlight); ?>">
			</label>
		</p>
		<p>
			<label>
				<?php _e('Use default CSS?', FORM_ID); ?>
				<input type="checkbox" name="<?php echo $this->get_field_name('default_css'); ?>"<?php echo ($default_css == 'on' ? ' checked' : ''); ?>>
			</label>
		</p>
	<?php
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
				wp_register_style('prefix-css', plugins_url('c/all.css', __FILE__));
				wp_enqueue_style('prefix-css');
			}
			?>
			<table class="statsfc_form">
				<thead>
					<tr>
						<th></th>
						<th>Team</th>
						<th class="statsfc_numeric">Last 6 Results</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($json as $row) {
						$classes = array();

						if (! empty($highlight) && $highlight == $row->team) {
							$classes[] = 'statsfc_highlight';
						}

						// {"position":1,"team_id":10260,"team":"Manchester United","form":["D","W","W","W","W","W"]}
						?>
						<tr<?php echo (! empty($classes) ? ' class="' . implode(' ', $classes) . '"' : ''); ?>>
							<td class="statsfc_numeric"><?php echo esc_attr($row->position); ?></td>
							<td class="statsfc_team statsfc_badge_<?php echo str_replace(' ', '', strtolower($row->team)); ?>"><?php echo esc_attr($row->teamshort); ?></td>
							<td class="statsfc_form_results"><?php
								foreach ($row->form as $result) {
									echo '<span class="statsfc_' . strtolower($result) . '">' . esc_attr($result) . '</span>';
								}
							?></td>
						</tr>
					<?php
					}
					?>
				</tbody>
			</table>

			<p class="statsfc_footer"><small>Powered by <a href="https://statsfc.com" target="_blank" title="Football widgets and API">StatsFC.com</a></small></p>
		<?php
		} catch (Exception $e) {
			echo '<p class="statsfc_error">' . esc_attr($e->getMessage()) .'</p>' . PHP_EOL;
		}

		echo $after_widget;
	}
}

// register StatsFC widget
add_action('widgets_init', create_function('', 'register_widget("' . FORM_ID . '");'));
?>