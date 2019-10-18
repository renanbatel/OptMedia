<?php
/**
 * Class Admin
 *
 * Everything that should be executed on the admin page of WordPress should be
 * part of this file.
 */

namespace OptMedia\Admin;

class Admin
{
    protected $options;

    public function __construct()
    {
        $this->options = (array) json_decode(get_option(OPTMEDIA_OPTIONS_NAME));
    }

    /**
     * Check if current page is plugin page
     *
     * @return boolean
     *
     * @since 0.1.0
     * @author Renan Batel <renanbatel@gmail.com>
     */
    private function isPluginPage()
    {
        return isset($_GET["page"]) && $_GET["page"] == OPTMEDIA_ADMIN_PAGE_SLUG;
    }

    /**
     * Add an options page under the Settings submenu
     *
     * @return void
     *
     * @since 0.1.0
     * @author Renan Batel <renanbatel@gmail.com>
     */
    public function addMenuPage()
    {
        $this->pluginScreenHookSuffix = add_menu_page(
            __("OptMedia Settings", OPTMEDIA_DOMAIN),
            __("OptMedia", OPTMEDIA_DOMAIN),
            $this->options["settings_userAccessLevel"],
            OPTMEDIA_ADMIN_PAGE_SLUG,
            [
                $this,
                "displaySettingsPage",
            ]
            // TODO: setup icon
            // plugin_dir_url( __FILE__ ) . "assets/logo-menu.png"
        );
    }

    /**
     * Include the settings page(s) for the plugin
     * * As we render a react application, we most likely need only one view file
     *
     * @return void
     *
     * @since 0.1.0
     * @author Renan Batel <renanbatel@gmail.com>
     */
    public function displaySettingsPage()
    {
        include_once "views/admin-index.php";
    }

    /**
     * Set admin stylesheets
     * Docs: https://developer.wordpress.org/reference/functions/wp_enqueue_style/
     *
     * @return void
     *
     * @since 0.1.0
     * @author Renan Batel <renanbatel@gmail.com>
     */
    public function enqueueStyles()
    {
        if ($this->isPluginPage()) {
            wp_enqueue_style(
                OPTMEDIA_NAME,
                plugin_dir_url(__FILE__) . "css/admin.styles.css",
                [],
                OPTMEDIA_VERSION,
                "all"
            );
        }
    }

    /**
     * Set admin Javascript
     * Docs: https://developer.wordpress.org/reference/functions/wp_enqueue_script/
     *
     * @return void
     *
     * @since 0.1.0
     * @author Renan Batel <renanbatel@gmail.com>
     */
    public function enqueueScripts()
    {
        if ($this->isPluginPage()) {
            wp_enqueue_script(
                OPTMEDIA_NAME,
                plugin_dir_url(__FILE__) . "js/admin.bundle.js",
                [],
                OPTMEDIA_VERSION,
                true
            );

          // inject some wp specific variables into the admin script, they are
          // available in the __OPTMEDIA__ object afterwards
            wp_localize_script(
                OPTMEDIA_NAME,
                "__OPTMEDIA__",
                [
                    "asset_path" => plugin_dir_url(OPTMEDIA_PLUGIN_FILE) . "static",
                    "language"   => get_bloginfo("language"),
                    "endpoint"   => esc_url_raw(rest_url()),
                    "namespace"  => OPTMEDIA_API_NAMESPACE,
                    "nonce"      => wp_create_nonce("wp_rest"),
                    "root"       => esc_url_raw(rest_url()),
                ]
            );
        }
    }
}
