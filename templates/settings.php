<?php
namespace Dudlewebs\DSLB;

defined('ABSPATH') || exit;

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';

register_setting('dslb_general_settings', 'dslb_settings');
register_setting('dslb_advanced_settings', 'dslb_settings');
?>

<div class="wrap">
    <h1><?= esc_html__('Settings', 'doctors-slot-booking'); ?></h1>
    <h2 class="nav-tab-wrapper">
        <a href="?post_type=product&page=dslb-settings&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
            <?= esc_html__('General', 'doctors-slot-booking'); ?>
        </a>
        <a href="?post_type=product&page=dslb-settings&tab=advanced" class="nav-tab <?php echo $active_tab === 'advanced' ? 'nav-tab-active' : ''; ?>">
            <?= esc_html__('Advanced', 'doctors-slot-booking'); ?>
        </a>
    </h2>
    <form method="post" action="options.php">
        <?php
        if ($active_tab === 'general') {
            settings_fields('dslb_general_settings');
            ?>
            <h2><?= esc_html__('General Settings', 'doctors-slot-booking'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?= esc_html__('Rewrite URL', 'doctors-slot-booking'); ?></th>
                    <td>
                        <input type="checkbox" name="dslb_settings[rewrite_url]" id="dslb_rewrite_url" value="1" <?php checked(dslb_get_option('rewrite_url'), 1); ?>>
                    </td>
                </tr>
            </table>
            <?php
        } elseif ($active_tab === 'advanced') {
            settings_fields('dslb_advanced_settings');
            ?>
            <h2><?= esc_html__('Advanced Settings', 'doctors-slot-booking'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?= esc_html__('Copy to Bucket', 'doctors-slot-booking'); ?></th>
                    <td>
                        <input type="checkbox" name="dslb_settings[copy_to_bucket]" id="dslb_copy_to_bucket" value="1" <?php checked(dslb_get_option('copy_to_bucket'), 1); ?>>
                    </td>
                </tr>
            </table>
            <?php
        }
        submit_button();
        ?>
    </form>
</div>

