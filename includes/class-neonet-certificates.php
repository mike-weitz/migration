<?php

namespace  NEOnet_Events\Certificates;

class Certificates
{
    protected $user;
    protected $user_id;
    protected $access_token;
    protected $api_url;
    protected $api_version;
    protected $api_endpoint;
    protected $api_token_endpoint;
    protected $api_client_id;
    protected $api_client_secret;
    protected $api_grant_type;
    protected $certificate_page_slug    = 'view-certificate';
    protected $jspdf_cdn                = "https://cdnjs.cloudflare.com/ajax/libs/jspdf/3.0.1/jspdf.umd.min.js";
    protected $certificates             = [
        [
            'id' => 1,
            'title' => 'Library Services --- Event with CEU',
            'description' => 'This was Library Services related event that Tamra Dugan hosted that granted attendees Continuing Education Units.',

            'ceu' => 3,
            'date' => '07-01-2025',
            'category' => 'ed_tech_tamra',
            'host' => 'Tamra Dugan'
        ],
        [
            'id' => 2,
            'title' => 'DASL Workshop -- Student Services Event',
            'description' => 'This was a Student Services related event  that granted attendees Continuing Education Units.',
            'ceu' => 1.5,
            'date' => '07-03-2025',
            'category' => 'student_services',
            'host' => 'none'
        ],
        [
            'id' => 3,
            'title' => 'Fiscal Class -- Event',
            'description' => 'This was a Fiscal Services related event that granted attendees Continuing Education Units.',
            'ceu' => 0.5,
            'date' => '07-08-2025',
            'category' => 'fiscal',
            'host' => 'none'
        ],
        [
            'id' => 5,
            'title' => 'Ed Tech -- Workshop (Dan)',
            'description' => 'This was an Ed Tech related event that Daniel Niessen hosted that granted attendees Continuing Education Units.',
            'ceu' => 0.5,
            'date' => '07-08-2025',
            'category' => 'ed_tech_daniel',
            'host' => 'none'
        ],
        [
            'id' => 4,
            'title' => 'Ed Tech Event -- Julia Hosting',
            'description' => 'This was an Ed Tech related event that Julia Tilton that granted attendees Continuing Education Units.',
            'ceu' => 1,
            'date' => '07-08-2025',
            'category' => 'ed_tech_julia',
            'host' => 'Julia Tilton'
        ],
        [
            'id' => 6,
            'title' => 'Governance Meeting -- CEU Available',
            'description' => 'So we can',
            'ceu' => 1,
            'date' => '08-01-2025',
            'category' => 'governance',
            'host' => 'nond'
        ],
        [
            'id' => 7,
            'title' => 'EMIS Event -- Workshop',
            'description' => 'This was an EMIS related event that granted attendees Continuing Education Units.',
            'ceu' => 1,
            'date' => '08-01-2025',
            'category' => 'emis',
            'host' => 'nond'
        ],
        [
            'id' => 9,
            'title' => 'Ed Tech -- Unspecified Event',
            'description' => 'This was an Ed Tech related event that granted attendees Continuing Education Units.',
            'ceu' => 1,
            'date' => '08-01-2025',
            'category' => 'ed_tech',
            'host' => 'nond'
        ],
    ];
    protected $redirect_url = "https://neonet.org/my-certificates";

    /**
     * Constructor
     * 
     */
    public function __construct()
    {
        $this->load_settings();

        define('NEONET_PDF_GENERATOR_SCRIPT_SLUG', "neonet-certificate-pdf-generator");
        define('NEONET_CERTIFICATE_DATA_GLOBAL_VARIABLE_NAME', 'neonetCertificate');
        define('NEONET_CERTIFICATE_NONCE_PARAM_NAME', '_certificate_nonce');

        add_shortcode('neonet_certificates', [$this, 'display_certificates']);

        // add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('init', [$this, 'register_certificate_page']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    /**
     * Load Settings
     * 
     * - Retrieve options information from WordPress
     * - Store variables in class 
     */
    protected function load_settings()
    {
        $api_url = trailingslashit(get_option('neonet_events_api_url'));
        $api_version = trailingslashit('v' . get_option('neonet_events_api_version'));

        $this->user_id = get_current_user_id();
        $this->user = wp_get_current_user();

        $this->api_endpoint = 'users/<user-guid>/attended-events';
        $this->api_token_endpoint = 'oauth/token';
        $this->api_url = $api_url;
        $this->api_version = $api_version;


        $this->api_client_id = NEONET_EVENTS_API_CLIENT_ID;
        $this->api_client_secret = NEONET_EVENTS_API_CLIENT_SECRET;
        $this->api_grant_type = NEONET_EVENTS_API_GRANT_TYPE;

        $this->get_access_token();
    }

    /**
     * Request Access Token
     * 
     */
    protected function get_access_token()
    {
        // Strucute URL
        $url = $this->api_url . $this->api_token_endpoint;

        // Strucute Post DATA
        $post_data = [
            "grant_type" => $this->api_grant_type,
            "client_id" => $this->api_client_id,
            "client_secret" => $this->api_client_secret
        ];

        // Structure full Post Body
        $post_body = [
            "headers" => [
                "Content-Type" => "application/json"
            ],
            "body" => json_encode($post_data)
        ];

        // // Make request to Events API for access token
        $response = wp_remote_post($url, $post_body);
        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body);

        // Check for errors
        if (! is_wp_error($response)) {
            if (! empty($json->access_token)) {
                $this->access_token = $json->access_token;
            } else {
                // Handle empty resposne
            }
        }
    }

    /**
     * Enqueue Scripts
     * 
     */
    public function enqueue_scripts()
    {
        if (is_page($this->certificate_page_slug)) {
            wp_enqueue_script(
                'jspdf-external-library',
                $this->jspdf_cdn,
                array(),
                null,
                false
            );
        }
    }

    /**
     * Register Certificate Page
     * 
     */
    public function register_certificate_page()
    {
        add_rewrite_rule('^view-certificate/?$', 'index.php?pagename=view-certificate', 'top');
        add_filter('query_vars', [$this, 'add_certificate_query_vars']);
        add_action('template_include', [$this, 'load_certificate_template']);
    }

    public function add_certificate_query_vars($vars)
    {
        $vars[] = 'event_id';
        $vars[] = '_certificate_nonce';
        return $vars;
    }

    /**
     * Get Nonce String 
     * 
     * Create a unique nonce string for viewing the certificate
     *  - _certificate_nonce
     *  - <event_id>
     *  - <current_user_id>
     * 
     */
    public function get_nonce_string($event_id)
    {
        return 'generate_certificate_' . $event_id . '_' . get_current_user_id();
    }

    /**
     * Create Nonce
     * 
     * Use Wordpress wp_create_nonce to generate a nonce
     * to view the certificate
     * 
     */
    public function create_nonce($event_id)
    {
        return wp_create_nonce($this->get_nonce_string($event_id));
    }

    /**
     * Verify Nonce
     * 
     * Use WordPress wp_verify_nonce to verify the provided
     * nonce when user views a certificate
     * 
     */
    public function verify_nonce($nonce, $event_id)
    {
        return wp_verify_nonce($nonce, $this->get_nonce_string($event_id));
    }

    /**
     * Fetch Attended Events
     * 
     */
    public function get_attended_events()
    {
        if (! $this->access_token) {
            // ERROR --> No access token stored
        } else {
            // // Get current user guid from WordPress
            $user_guid = get_user_meta($this->user_id, 'mo_ldap_local_custom_attribute_objectguid', true);

            // Generate the fully qualified URL
            $url = $this->api_url . $this->api_version . 'users/' . $user_guid . '/attended-events';
            $url = $this->api_url . $this->api_version . 'users/6069dd02134ff84083e92845b84e596b/attended-events';

            // Create the body of the post
            $post_body = [
                "headers" => [
                    "Content-Type" => "application/json",
                    "Authorization" => "Bearer " . $this->access_token
                ]
            ];

            //  Send request for attended events data
            $response = wp_remote_get($url, $post_body);
            $body = wp_remote_retrieve_body($response);
            $json = json_decode($body);

            // Return the list of attended events
            $result = [];
            if (! empty($json->data)) {

                $result = $json->data;
            } else {
                $result =  $this->certificates;
            }

            usort($result, function ($a, $b) {
                $dateA = strtotime($a['date']);
                $dateB = strtotime($b['date']);

                // For descending order, return 1 if $a's date is older than $b's, -1 if newer, 0 if same.
                return $dateB - $dateA;
            });

            return $result;
        }
    }

    /**
     * Fetch Certificate Data
     *  
     */
    public function get_certificate_data($event_id)
    {
        // Make request for event data
        //

        // Return event data
        //
        foreach ($this->certificates as $event) {
            if (isset($event["id"]) && $event["id"] === $event_id) {
                return $event;
            }
        }
        return null;
    }

    /**
     * Display Certificates
     * 
     * Fetch event attendance data from Events API and list
     * the results for the current user.
     * 
     * Include a nonce for security when viewing the certificate
     * 
     */
    public function display_certificates()
    {
        $attended_events = $this->get_attended_events();

        $output = "<div class='neonet-api-certificates'>";
        $output .= "<ul>";

        foreach ($attended_events as $event) {
            $certificate_url = add_query_arg(
                array(
                    'event_id' => $event['id'],
                    '_certificate_nonce' => $this->create_nonce($event["id"]),
                ),
                '/view-certificate/'
            );

            $event_date = date_format(date_create($event["date"]), 'F d, Y');

            $output .= "<li>";
            $output .= '<a href="' . esc_url($certificate_url) . '">';
            $output .= '<span class="neonet-api-certificate-title">';
            $output .=  esc_html($event['title']);
            $output .= '</span>';
            $output .=  " - ";
            $output .=  $event_date;
            $output .= '</a>';
            $output .= "</li>";
        }

        $output .= "</ul>";
        $output .= "</div>";

        return $output;
    }


    /**
     * Display Certificate (single)
     * 
     */
    public function display_certificate()
    {
        // Ensure the proper params are present in URL
        if (isset($_GET['event_id']) && isset($_GET['_certificate_nonce'])) {

            // Sanitize the parameters
            $event_id = absint($_GET['event_id']);
            $nonce = sanitize_text_field($_GET['_certificate_nonce']);

            // Verify that the provided nonce is valid
            if ($this->verify_nonce($nonce, $event_id)) {
                // Proceed to fetch certificate data from the API
                $certificate_data = $this->get_certificate_data($event_id);

                if (! $certificate_data) {
                    $this->print_invalid_request();
                    die();
                }

                // Enqueue your JavaScript file that contains the jsPDF logic
                wp_enqueue_script(
                    NEONET_PDF_GENERATOR_SCRIPT_SLUG,
                    plugin_dir_url(dirname(__FILE__)) . 'js/index.js',
                    array('jquery'),
                    null,
                    true
                );

                // Localize the certificate data for JavaScript code
                wp_localize_script(
                    NEONET_PDF_GENERATOR_SCRIPT_SLUG,
                    NEONET_CERTIFICATE_DATA_GLOBAL_VARIABLE_NAME,
                    [
                        'currentUser' => wp_get_current_user()->data,
                        'data' => $certificate_data,
                        'redirectUrl' => $this->redirect_url
                    ]
                );
            } else {
                // Invalid nonce, display an error
                $this->print_invalid_request();
            }
        } else {
            // Missing parameters
            $this->print_invalid_request();
            die();
        }
    }


    /**
     * Load Certificate Template
     * 
     */
    public function load_certificate_template($template)
    {
        if (get_query_var('pagename') === 'view-certificate') {
            $new_template = plugin_dir_path(__FILE__) . '../templates/certificate-template.php';
            if (file_exists($new_template)) {
                $this->display_certificate();
                return $new_template;
            }
        }
        return $template;
    }

    /**
     * Print Invalid Request
     * 
     */
    public function print_invalid_request()
    {
        echo '<p class="error">Invalid certificate request.</p>';
    }
}
