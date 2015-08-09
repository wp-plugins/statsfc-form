<?php
/*
Plugin Name: StatsFC Form
Plugin URI: https://statsfc.com/widgets/form
Description: StatsFC Form Guide
Version: 1.7.3
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

define('STATSFC_FORM_ID',      'StatsFC_Form');
define('STATSFC_FORM_NAME',    'StatsFC Form');
define('STATSFC_FORM_VERSION', '1.7.3');

/**
 * Adds StatsFC widget.
 */
class StatsFC_Form extends WP_Widget
{
    public $isShortcode = false;

    protected static $count = 0;

    private static $defaults = array(
        'title'       => '',
        'key'         => '',
        'competition' => '',
        'date'        => '',
        'highlight'   => '',
        'show_badges' => true,
        'show_score'  => true,
        'limit'       => 0,
        'default_css' => true
    );

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(STATSFC_FORM_ID, STATSFC_FORM_NAME, array('description' => 'StatsFC League Table'));
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance)
    {
        $instance    = wp_parse_args((array) $instance, self::$defaults);
        $title       = strip_tags($instance['title']);
        $key         = strip_tags($instance['key']);
        $competition = strip_tags($instance['competition']);
        $date        = strip_tags($instance['date']);
        $highlight   = strip_tags($instance['highlight']);
        $show_badges = strip_tags($instance['show_badges']);
        $show_score  = strip_tags($instance['show_score']);
        $limit       = strip_tags($instance['limit']);
        $default_css = strip_tags($instance['default_css']);
        ?>
        <p>
            <label>
                <?php _e('Title', STATSFC_FORM_ID); ?>
                <input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
            </label>
        </p>
        <p>
            <label>
                <?php _e('Key', STATSFC_FORM_ID); ?>
                <input class="widefat" name="<?php echo $this->get_field_name('key'); ?>" type="text" value="<?php echo esc_attr($key); ?>">
            </label>
        </p>
        <p>
            <label>
                <?php _e('Competition', STATSFC_FORM_ID); ?>
                <input class="widefat" name="<?php echo $this->get_field_name('competition'); ?>" type="text" value="<?php echo esc_attr($competition); ?>" placeholder="e.g., EPL, CHP, FAC">
            </label>
        </p>
        <p>
            <label>
                <?php _e('Date', STATSFC_FORM_ID); ?>
                <input class="widefat" name="<?php echo $this->get_field_name('date'); ?>" type="text" value="<?php echo esc_attr($date); ?>" placeholder="YYYY-MM-DD">
            </label>
        </p>
        <p>
            <label>
                <?php _e('Highlight team', STATSFC_FORM_ID); ?>
                <input class="widefat" name="<?php echo $this->get_field_name('highlight'); ?>" type="text" value="<?php echo esc_attr($highlight); ?>" placeholder="e.g., Liverpool, Manchester City">
            </label>
        </p>
        <p>
            <label>
                <?php _e('Show badges?', STATSFC_FORM_ID); ?>
                <input type="checkbox" name="<?php echo $this->get_field_name('show_badges'); ?>"<?php echo ($show_badges == 'on' ? ' checked' : ''); ?>>
            </label>
        </p>
        <p>
            <label>
                <?php _e('Show match scores?', STATSFC_FORM_ID); ?>
                <input type="checkbox" name="<?php echo $this->get_field_name('show_score'); ?>"<?php echo ($show_score == 'on' ? ' checked' : ''); ?>>
            </label>
        </p>
        <p>
            <label>
                <?php _e('Limit', STATSFC_FORM_ID); ?>
                <input class="widefat" name="<?php echo $this->get_field_name('limit'); ?>" type="number" value="<?php echo esc_attr($limit); ?>" min="0" max="99">
            </label>
        </p>
        <p>
            <label>
                <?php _e('Use default styles?', STATSFC_FORM_ID); ?>
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
    public function update($new_instance, $old_instance)
    {
        $instance                = $old_instance;
        $instance['title']       = strip_tags($new_instance['title']);
        $instance['key']         = strip_tags($new_instance['key']);
        $instance['competition'] = strip_tags($new_instance['competition']);
        $instance['date']        = strip_tags($new_instance['date']);
        $instance['highlight']   = strip_tags($new_instance['highlight']);
        $instance['show_badges'] = strip_tags($new_instance['show_badges']);
        $instance['show_score']  = strip_tags($new_instance['show_score']);
        $instance['limit']       = strip_tags($new_instance['limit']);
        $instance['default_css'] = strip_tags($new_instance['default_css']);

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
    public function widget($args, $instance)
    {
        extract($args);

        $title       = apply_filters('widget_title', $instance['title']);
        $whitelist   = array('competition', 'date', 'highlight', 'showBadges', 'showScore', 'limit');
        $unique_id   = ++static::$count;
        $key         = $instance['key'];
        $referer     = (array_key_exists('HTTP_HOST', $_SERVER) ? $_SERVER['HTTP_HOST'] : '');
        $default_css = filter_var($instance['default_css'], FILTER_VALIDATE_BOOLEAN);

        $options = array(
            'competition' => $instance['competition'],
            'date'        => $instance['date'],
            'highlight'   => $instance['highlight'],
            'showBadges'  => (filter_var($instance['show_badges'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false'),
            'showScore'   => (filter_var($instance['show_score'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false'),
            'limit'       => (int) $instance['limit']
        );

        $html  = $before_widget;
        $html .= $before_title . $title . $after_title;
        $html .= '<div id="statsfc-form-' . $unique_id . '"></div>' . PHP_EOL;
        $html .= $after_widget;

        // Enqueue CSS
        if ($default_css) {
            wp_register_style(STATSFC_FORM_ID . '-css', plugins_url('all.css', __FILE__), null, STATSFC_FORM_VERSION);
            wp_enqueue_style(STATSFC_FORM_ID . '-css');
        }

        // Enqueue base JS
        wp_register_script(STATSFC_FORM_ID . '-js', plugins_url('form.js', __FILE__), array('jquery'), STATSFC_FORM_VERSION, true);
        wp_enqueue_script(STATSFC_FORM_ID . '-js');

        // Enqueue widget JS
        $object = 'statsfc_form_' . $unique_id;

        $script  = '<script>' . PHP_EOL;
        $script .= 'var ' . $object . ' = new StatsFC_Form(' . json_encode($key) . ');' . PHP_EOL;
        $script .= $object . '.referer = ' . json_encode($referer) . ';' . PHP_EOL;

        foreach ($whitelist as $parameter) {
            if (! array_key_exists($parameter, $options)) {
                continue;
            }

            $script .= $object . '.' . $parameter . ' = ' . json_encode($options[$parameter]) . ';' . PHP_EOL;
        }

        $script .= $object . '.display("statsfc-form-' . $unique_id . '");' . PHP_EOL;
        $script .= '</script>';

        add_action('wp_print_footer_scripts', function() use ($script)
        {
            echo $script;
        });

        if ($this->isShortcode) {
            return $html;
        } else {
            echo $html;
        }
    }

    public static function shortcode($atts)
    {
        $args = shortcode_atts(self::$defaults, $atts);

        $widget              = new self;
        $widget->isShortcode = true;

        return $widget->widget(array(), $args);
    }
}

// register StatsFC widget
add_action('widgets_init', function()
{
    register_widget(STATSFC_FORM_ID);
});

add_shortcode('statsfc-form', STATSFC_FORM_ID . '::shortcode');
