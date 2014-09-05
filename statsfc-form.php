<?php
/*
Plugin Name: StatsFC Form
Plugin URI: https://statsfc.com/docs/wordpress
Description: StatsFC Form Guide
Version: 1.5.5
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
	public $isShortcode = false;

	private static $defaults = array(
		'title'			=> '',
		'key'			=> '',
		'competition'	=> '',
		'team'			=> '',
		'date'			=> '',
		'limit'			=> 0,
		'highlight'		=> '',
		'default_css'	=> true
	);

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
		$instance		= wp_parse_args((array) $instance, self::$defaults);
		$title			= strip_tags($instance['title']);
		$key			= strip_tags($instance['key']);
		$competition	= strip_tags($instance['competition']);
		$team			= strip_tags($instance['team']);
		$date			= strip_tags($instance['date']);
		$limit			= strip_tags($instance['limit']);
		$highlight		= strip_tags($instance['highlight']);
		$default_css	= strip_tags($instance['default_css']);
		?>
		<p>
			<label>
				<?php _e('Title', STATSFC_FORM_ID); ?>:
				<input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
			</label>
		</p>
		<p>
			<label>
				<?php _e('Key', STATSFC_FORM_ID); ?>:
				<input class="widefat" name="<?php echo $this->get_field_name('key'); ?>" type="text" value="<?php echo esc_attr($key); ?>">
			</label>
		</p>
		<p>
			<label>
				<?php _e('Competition', STATSFC_FORM_ID); ?>:
				<?php
				try {
					$data = $this->_fetchData('https://api.statsfc.com/crowdscores/competitions.php?type=League');

					if (empty($data)) {
						throw new Exception;
					}

					$json = json_decode($data);

					if (isset($json->error)) {
						throw new Exception;
					}
					?>
					<select class="widefat" name="<?php echo $this->get_field_name('competition'); ?>">
						<option></option>
						<?php
						foreach ($json as $comp) {
							echo '<option value="' . esc_attr($comp->key) . '"' . ($comp->key == $competition ? ' selected' : '') . '>' . esc_attr($comp->name) . '</option>' . PHP_EOL;
						}
						?>
					</select>
				<?php
				} catch (Exception $e) {
				?>
					<input class="widefat" name="<?php echo $this->get_field_name('competition'); ?>" type="text" value="<?php echo esc_attr($competition); ?>">
				<?php
				}
				?>
			</label>
		</p>
		<p>
			<label>
				<?php _e('Team', STATSFC_FORM_ID); ?>:
				<input class="widefat" name="<?php echo $this->get_field_name('team'); ?>" type="text" value="<?php echo esc_attr($team); ?>">
			</label>
		</p>
		<p>
			<label>
				<?php _e('Date (YYYY-MM-DD)', STATSFC_FORM_ID); ?>:
				<input class="widefat" name="<?php echo $this->get_field_name('date'); ?>" type="text" value="<?php echo esc_attr($date); ?>" placeholder="YYYY-MM-DD">
			</label>
		</p>
		<p>
			<label>
				<?php _e('Limit', STATSFC_FORM_ID); ?>:
				<input class="widefat" name="<?php echo $this->get_field_name('limit'); ?>" type="number" value="<?php echo esc_attr($limit); ?>" min="0" max="99">
			</label>
		</p>
		<p>
			<label>
				<?php _e('Highlight', STATSFC_FORM_ID); ?>:
				<input class="widefat" name="<?php echo $this->get_field_name('highlight'); ?>" type="text" value="<?php echo esc_attr($highlight); ?>">
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
		$instance['key']			= strip_tags($new_instance['key']);
		$instance['competition']	= strip_tags($new_instance['competition']);
		$instance['team']			= strip_tags($new_instance['team']);
		$instance['date']			= strip_tags($new_instance['date']);
		$instance['limit']			= strip_tags($new_instance['limit']);
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
		$key			= $instance['key'];
		$competition	= $instance['competition'];
		$team			= $instance['team'];
		$date			= $instance['date'];
		$limit			= $instance['limit'];
		$highlight		= $instance['highlight'];
		$default_css	= filter_var($instance['default_css'], FILTER_VALIDATE_BOOLEAN);

		$html  = $before_widget;
		$html .= $before_title . $title . $after_title;

		try {
			$data = $this->_fetchData('https://api.statsfc.com/crowdscores/form.php?key=' . urlencode($key) . '&competition=' . urlencode($competition) . '&date=' . urlencode($date) . '&limit=' . urlencode($limit));

			if (empty($data)) {
				throw new Exception('There was an error connecting to the StatsFC API');
			}

			$json = json_decode($data);

			if (isset($json->error)) {
				throw new Exception($json->error);
			}

			$form		= $json->form;
			$customer	= $json->customer;

			if ($default_css) {
				wp_register_style(STATSFC_FORM_ID . '-css', plugins_url('all.css', __FILE__));
				wp_enqueue_style(STATSFC_FORM_ID . '-css');
			}

			$html .= <<< HTML
			<div class="statsfc_form">
				<table>
HTML;

			if (empty($team)) {
				$html .= <<< HTML
				<thead>
					<tr>
						<th></th>
						<th>Team</th>
						<th colspan="6">Form</th>
					</tr>
				</thead>
HTML;
			}

			$html .= '<tbody>';

			foreach ($form as $row) {
				if (! empty($team) && $team !== $row->team) {
					continue;
				}

				$classes = array();

				if (! empty($highlight) && $highlight == $row->team) {
					$classes[] = 'statsfc_highlight';
				}

				if (count($row->form) < 6) {
					for ($i = count($row->form); $i < 6; $i++) {
						$row->form[] = null;
					}
				}

				$class = (! empty($classes) ? ' class="' . implode(' ', $classes) . '"' : '');

				$html .= '<tr' . $class . '>' . PHP_EOL;

				if (empty($team)) {
					$html .= '<td class="statsfc_numeric">' . esc_attr($row->pos) . '</td>' . PHP_EOL;
				}

				$html .= '<td class="statsfc_team statsfc_badge statsfc_badge_' . esc_attr($row->path) . '" style="background-image: url(//api.statsfc.com/kit/' . esc_attr($row->path) . '.svg);">' . esc_attr($row->team) . '</td>' . PHP_EOL;

				foreach ($row->form as $result) {
					$html .= '<td class="statsfc_form_results">' . PHP_EOL;

					if (! empty($result->result)) {
						$html .= '<span class="statsfc_' . strtolower($result->result) . '">' . esc_attr($result->result) . '</span>' . PHP_EOL;
					}

					$html .= '</td>' . PHP_EOL;
				}

				$html .= '</tr>' . PHP_EOL;
			}

			$html .= <<< HTML
					</tbody>
				</table>
HTML;

			if ($customer->advert) {
				$html .= <<< HTML
				<p class="statsfc_footer"><small>Powered by StatsFC.com. Fan data via CrowdScores.com</small></p>
HTML;
			}

			$html .= <<< HTML
			</div>
HTML;
		} catch (Exception $e) {
			$html .= '<p style="text-align: center;">StatsFC.com – ' . esc_attr($e->getMessage()) . '</p>' . PHP_EOL;
		}

		$html .= $after_widget;

		if ($this->isShortcode) {
			return $html;
		} else {
			echo $html;
		}
	}

	private function _fetchData($url) {
		$response = wp_remote_get($url);

		return wp_remote_retrieve_body($response);
	}

	public static function shortcode($atts) {
		$args = shortcode_atts(self::$defaults, $atts);

		$widget					= new self;
		$widget->isShortcode	= true;

		return $widget->widget(array(), $args);
	}
}

// register StatsFC widget
add_action('widgets_init', create_function('', 'register_widget("' . STATSFC_FORM_ID . '");'));
add_shortcode('statsfc-form', STATSFC_FORM_ID . '::shortcode');
