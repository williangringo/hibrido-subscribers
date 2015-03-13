<?php
/*
Plugin Name: hibrido subscribers
Plugin URI: http://www.souhibrido.com.br/
Description: um plugin para cadastrar subscribers em uma tabela na newsletter e poder administra-los no admin
Version: 0.1.0
Author: hibrido
Author URI: http://www.souhibrido.com.br/
License: GPL
Text Domain: hibrido_subscribers
*/

define('HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN', 'hibrido_subscribers');

load_plugin_textdomain(HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages/');

class HibridoSubscribers
{
    /**
     *
     */
    const VERSION = '0.1.0';
    /**
     *
     */
    const DB_VERSION = 1;

    /**
     *
     */
    const AJAX_ACTION           = 'hibrido_subscribers_ajax_action';
    /**
     *
     */
    const AJAX_OBJECT           = 'hibrido_subscribers_ajax_object';
    /**
     *
     */
    const AJAX_NONCE            = 'hibrido_subscribers_ajax_nonce';
    /**
     *
     */
    const AJAX_CAP              = 'hibrido_subscribers_ajax_cap';
    /**
     *
     */
    const AJAX_MENU_SLUG        = 'hibrido_subscribers_ajax_menu_slug';
    /**
     *
     */
    const AJAX_SUBMENU_CSV_SLUG = 'hibrido_subscribers_ajax_submenu_csv_slug';

    /**
     *
     */
    const NOTICE_ERROR = 'hibrido_subscribers_notice_error';

    /**
     * tableName
     *
     * @var mixed
     * @access private
     */
    private $tableName = 'hibrido_subscribers';

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix . $this->tableName;
    }

    /**
     * bootstrap function.
     *
     * @access public
     * @return void
     */
    public function bootstrap()
    {
        // activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));

        // deactivation hook
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // scripts
        add_action('wp_enqueue_scripts', array($this, 'scripts'));

        // make de action
        add_action('wp_ajax_' . self::AJAX_ACTION, array($this, 'respond'));
        add_action('wp_ajax_nopriv_' . self::AJAX_ACTION, array($this, 'respond'));

        // adiciona pagina admin
        add_action('admin_menu', array($this, 'addMenuPage'));

        // se for csv forçamos o download
        add_action('admin_init', array($this, 'exportCsv'));

        // mostra notificações se tiver alguma
        add_action('admin_notices', array($this, 'showNotices'));

        // adiciona automaticamente endereços de email que enviaram mensagens pelo wp_mail
        add_action('wp_mail', array($this, 'ninjaAddFromWpMail'));
    }

    /**
     * showNotices function.
     *
     * @access public
     * @return void
     */
    public function showNotices()
    {
        if ( ! isset($_GET[self::NOTICE_ERROR])) {
            return;
        }

        echo '<div class="error" id="' . self::NOTICE_ERROR . '"><p>' . $_GET[self::NOTICE_ERROR] . '</p></div>';
    }

    /**
     * addMenuPage function.
     *
     * @access public
     * @return void
     */
    public function addMenuPage()
    {
        add_menu_page(
            __('Subscribers', HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN),
            __('Subscribers', HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN),
            self::AJAX_CAP,
            self::AJAX_MENU_SLUG,
            function () { require __DIR__ . '/admin.php'; },
            plugins_url('hibrido_subscribers_admin_icon.png', __FILE__),
            30
        );

        add_submenu_page(
            self::AJAX_MENU_SLUG,
            __('Download .csv', HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN),
            __('Download .csv', HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN),
            self::AJAX_CAP,
            self::AJAX_SUBMENU_CSV_SLUG,
            function () {}
        );
    }

    /**
     * exportCsv function.
     *
     * @access private
     * @return void
     */
    public function exportCsv()
    {
        if ( ! isset($_GET['page']) || $_GET['page'] != self::AJAX_SUBMENU_CSV_SLUG) {
            return;
        }

        $rows = $this->getAll();

        if ($rows) {
            // headers
            header("Content-type: application/force-download");
            header('Content-Disposition: inline; filename="hibrido_subscribers_' . date('Y-m-d_H-i-s') . '.csv"');

            echo 'email' . PHP_EOL;

            foreach ($rows as $row) {
                echo $row->email . PHP_EOL;
            }
        } else {
            $url  = menu_page_url(self::AJAX_MENU_SLUG, false);
            $url .= '&' . self::NOTICE_ERROR . '=' . urlencode(__('No subscribers have been added to the list', HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN));
            wp_redirect($url);
        }

        die;
    }

    /**
     * deactivate function.
     *
     * @access public
     * @return void
     */
    public function deactivate()
    {
        $this->removeCap();
    }

    /**
     * removeCap function.
     *
     * @access private
     * @return void
     */
    private function removeCap()
    {
        $role = get_role('administrator');
        $role->remove_cap(self::AJAX_CAP);
    }

    /**
     * activate function.
     *
     * @access public
     * @return void
     */
    public function activate()
    {
        $this->createTable();
        $this->versionate();
        $this->addCap();
    }

    /**
     * createTable function.
     *
     * @access public
     * @return void
     */
    private function createTable()
    {
        global $wpdb;

        $ddl = "CREATE TABLE IF NOT EXISTS " . $this->tableName . " (
            id INT(9) NOT NULL AUTO_INCREMENT,
            email VARCHAR(200) NOT NULL,
            UNIQUE KEY id (id)
        );";

        $wpdb->query($ddl);
    }

    /**
     * versionate function.
     *
     * @access public
     * @return void
     */
    private function versionate()
    {
        update_option('hibrido_subscribers_version', self::VERSION);
        add_option('hibrido_subscribers_db_version', self::DB_VERSION);
    }

    /**
     * addCap function.
     *
     * @access private
     * @return void
     */
    private function addCap()
    {
        $role = get_role('administrator');
        $role->add_cap(self::AJAX_CAP);
    }

    /**
     * scripts function.
     *
     * @access public
     * @return void
     */
    public function scripts()
    {
        // main js
        wp_enqueue_script('hibrido-subscribers-ajax', plugin_dir_url(__FILE__) . 'ajax.js', array('jquery', 'jquery-form'), self::VERSION, true);

        // dados para o main
        wp_localize_script('hibrido-subscribers-ajax', self::AJAX_OBJECT, array(
            'action'            => self::AJAX_ACTION,
            'nonce'             => wp_create_nonce(self::AJAX_NONCE),
            'loadingButtonText' => __('Sending ...', HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN)
        ));
    }

    /**
     * respond function.
     *
     * @access public
     * @return void
     */
    public function respond()
    {
        if (isset($_POST['email'])) {
            if (is_email($_POST['email'])) {
                if ($this->add($_POST['email'])) {
                    $respond = array(
                        'success' => true,
                        'msg' => __('Your email was successfully added to our mailing list', HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN)
                    );
                } else {
                    $respond = array(
                        'success' => false,
                        'msg' => __('This email address was already added to our mailing list', HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN)
                    );
                }
            } else {
                $respond = array(
                    'success' => false,
                    'msg' => __('Please enter a valid email address', HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN)
                );
            }
        } else {
            $respond = array(
                'success' => false,
                'msg' => __('Please enter your email address', HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN)
            );
        }

        header('Content-type: application/json');
        echo json_encode($respond);

        die;
    }

    /**
     * add function.
     *
     * @access public
     * @param mixed $email
     * @return void
     */
    public function add($email)
    {
        global $wpdb;

        // verificamos se ele já existe
        if ($this->get($email)) {
            return false;
        }

        // senão insere
        $wpdb->insert($this->tableName, compact('email'));

        return $wpdb->insert_id;
    }

    /**
     * get function.
     *
     * @access public
     * @param mixed $email
     * @return void
     */
    public function get($email)
    {
        global $wpdb;

        $row = $wpdb->get_row('select * from ' . $this->tableName . ' where ' . $wpdb->prepare('email = %s', $email), OBJECT);

        return is_null($row) ? false : $row;
    }

    /**
     * getAll function.
     *
     * @access public
     * @return void
     */
    public function getAll()
    {
        global $wpdb;

        $rows = $wpdb->get_results('select * from '. $this->tableName .' order by id desc', OBJECT);

        return $wpdb->num_rows > 0 ? $rows : false;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function ninjaAddFromWpMail($data)
    {
        if ( ! empty($data['headers']) && is_array($data['headers'])) {
            $headers = $data['headers'];

            foreach ($headers as $header) {
                if (false === stripos($header, 'Reply-to:') && false === stripos($header, 'From:')) {
                    continue;
                }

                $email = preg_replace(array('#reply-to:#i', '#from:#i'), '', $header);
                $email = trim($email);


                if (false !== stripos($email, '<') && false !== stripos($email, '>')) {
                    $email = preg_replace(array('#(.*)<#i', '#>(.*)#i'), '', $email);
                }


                $email = sanitize_email($email);

                if ( ! is_email($email)) {
                    continue;
                }

                $this->add($email);
            }
        }

        // retornamos o argumento, pois, esse método é ligado
        // a um filtro e não a uma action
        return $data;
    }
}

global $hibridoSubscribers;
$hibridoSubscribers = new HibridoSubscribers();
$hibridoSubscribers->bootstrap();
