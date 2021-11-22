<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


/**
 * Class Zume_Coaching_Checklist_Magic_Link
 */
class Zume_Coaching_Checklist_Magic_Link extends DT_Magic_Url_Base {

    public $magic = false;
    public $parts = false;
    public $page_title = 'Zúme Coaching Checklist';
    public $page_description = 'Zúme personal coaching checklist.';
    public $root = "zume_app";
    public $type = 'coaching_checklist';
    public $post_type = 'contacts';
    private $meta_key = '';

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        $this->meta_key = $this->root . '_' . $this->type . '_magic_key';
        parent::__construct();

        /**
         * post type and module section
         */
        add_action( 'rest_api_init', [ $this, 'add_endpoints' ] );
        add_filter( 'dt_settings_apps_list', [ $this, 'dt_settings_apps_list' ], 10, 1 );

        /**
         * tests if other URL
         */
        $url = dt_get_url_path();
        if ( strpos( $url, $this->root . '/' . $this->type ) === false ) {
            return;
        }
        /**
         * tests magic link parts are registered and have valid elements
         */
        if ( !$this->check_parts_match() ){
            return;
        }

        // load if valid url
        add_action( 'dt_blank_body', [ $this, 'body' ] ); // body for no post key
        add_filter( 'dt_magic_url_base_allowed_css', [ $this, 'dt_magic_url_base_allowed_css' ], 10, 1 );
        add_filter( 'dt_magic_url_base_allowed_js', [ $this, 'dt_magic_url_base_allowed_js' ], 10, 1 );

    }

    public function dt_magic_url_base_allowed_js( $allowed_js ) {
        return $allowed_js;
    }

    public function dt_magic_url_base_allowed_css( $allowed_css ) {
        return $allowed_css;
    }

    /**
     * Writes custom styles to header
     *
     * @see DT_Magic_Url_Base()->header_style() for default state
     */
    public function header_style(){
        ?>
        <style>
            body {
                background-color: white;
                padding: 1em;
            }
        </style>
        <?php
    }

    /**
     * Writes javascript to the header
     *
     * @see DT_Magic_Url_Base()->header_javascript() for default state
     */
    public function header_javascript(){
        ?>
        <script>
            // console.log('insert header_javascript')
        </script>
        <?php
    }

    /**
     * Writes javascript to the footer
     *
     * @see DT_Magic_Url_Base()->footer_javascript() for default state
     */
    public function footer_javascript(){
        ?>
        <script>
            // console.log('insert footer_javascript')

            let jsObject = [<?php echo json_encode([
                'map_key' => DT_Mapbox_API::get_key(),
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'parts' => $this->parts,
                'translations' => [
                    'add' => __( 'Add Magic', 'disciple-tools-plugin-starter-template' ),
                ],
            ]) ?>][0]

            jQuery(document).ready(function($){
                $('.dt_multi_select').on("click", function (e){
                    $(this).addClass("loading")
                    let key = $(this).data('field-key')
                    let item = $(this).data('option-key')
                    let btn = $('.'+key+'_'+item)

                    let turn_off = false
                    if (btn.hasClass('selected-select-button')){
                        turn_off = true
                        btn.removeClass('selected-select-button')
                        btn.addClass('empty-select-button')
                    } else {
                        btn.addClass('selected-select-button')
                        btn.removeClass('empty-select-button')
                    }

                    window.makeRequest( "POST", jsObject.parts.type, { parts: jsObject.parts, field_key: key, option_value: item, turn_off: turn_off }, jsObject.parts.root + '/v1/' ).done(function(data){
                        if ( 'on' === data ) {
                            btn.addClass('selected-select-button')
                            btn.removeClass('empty-select-button')
                        } else {
                            btn.removeClass('selected-select-button')
                            btn.addClass('empty-select-button')
                        }
                    })
                    .fail(function(e) {
                        console.log(e)
                        jQuery('#error').html(e)
                    })
                })
                $('.ost_button').on('click', function(e){
                    let fk = jQuery(this).data('field-key')
                    let btn = jQuery('.'+fk+'_h')
                    if ( ! ( btn.hasClass('selected-select-button') || btn.hasClass('added')  ) ){
                        btn.addClass('added').click()
                    }
                })
                jQuery('.coaching-checklist-modal-open').on('click', function(){
                    let ccurl = jQuery(this).data('value')
                    jQuery('#modal-large-cc').foundation('open')
                    jQuery('#modal-large-cc-content').empty().append(`<iframe src="${ccurl}" style="width:100%;height:${window.innerHeight - 85}px;border:0;"></iframe>`)
                })
                jQuery('.additional-close').on('click', function(){
                    jQuery('#modal-large-cc').foundation('close')
                })
            })

        </script>
        <?php
        return true;
    }

    public function body(){
        $post_type = 'contacts';
        $post_fields = DT_Posts::get_post_field_settings( $post_type, true );
        $post = DT_Posts::get_post( $post_type, $this->parts['post_id'], false, false, true );
        if ( is_wp_error( $post ) ) {
            dt_write_log( $post );
            return;
        }
        $zume_coaching_checklist_items = zume_coaching_checklist_items();
        ?>
        <div id="custom-style"></div>
        <div id="wrapper">
            <div class="grid-x" style="width: 100%;max-width:400px; margin: 0 auto;">
                <div class="cell center">
                    <h2 id="title">Zúme Coaching Checklist</h2>
                    <p><?php echo esc_html( $post['name'] ) ?></p>
                </div>
                <div class="cell">

                    <p><strong>Concepts</strong></p>
                    <?php
                    foreach ($post_fields as $field_key => $field_options ) :
                        if ( isset( $field_options["tile"] ) && $field_options["tile"] === "zume_coaching_checklist" ) :
                            $string = explode( '_', $field_key );
                            $id = $string[3];

                            if ( 'concept' === $zume_coaching_checklist_items[$id]['type'] ) :
                                zume_write_checklist_row( $post, $post_fields, $field_key, $field_options, $zume_coaching_checklist_items[$id] );
                            endif;
                        endif;
                    endforeach;
                    ?>
                    <p><strong>Tools</strong></p>
                    <?php
                    foreach ($post_fields as $field_key => $field_options ) :
                        if ( isset( $field_options["tile"] ) && $field_options["tile"] === "zume_coaching_checklist" ) :
                            $string = explode( '_', $field_key );
                            $id = $string[3];
                            if ( 'tool' === $zume_coaching_checklist_items[$id]['type'] ) :
                                zume_write_checklist_row( $post, $post_fields, $field_key, $field_options, $zume_coaching_checklist_items[$id] );
                            endif;
                        endif;
                    endforeach;
                    ?>

                    <?php
                    $total_done = 0;
                    $total = 0;
                    foreach ($post_fields as $field_key => $field_options ) {
                        if ( isset( $field_options["tile"] ) && $field_options["tile"] === "zume_coaching_checklist" ) {
                            $total += sizeof( $field_options["default"] );
                            if ( isset( $post[$field_key] ) ){
                                $total_done += sizeof( $post[$field_key] );
                            }
                        }
                    }
                    ?>
                    <p><?php esc_html_e( 'Completed', 'zume-coaching-checklist' ); ?> <?php echo esc_html( $total_done ); ?>/<?php echo esc_html( $total ); ?></p>
                    <hr>

                </div>
                <div class="cell">
                    <div class="grid-x grid-padding-x grid-padding-y">
                        <div class="cell small-1">
                            <button type="button" class="dt_multi_select empty-select-button select-button button" style="padding:5px" >H</button>
                        </div>
                        <div class="cell small-4">
                            <h2><strong>H</strong>eard</h2>
                        </div>
                        <div class="cell small-7">
                            Have you heard about the concept or skill? (If not, you can click the link and review.)
                        </div>
                        <div class="cell small-1">
                            <button type="button" class="dt_multi_select empty-select-button select-button button" style="padding:5px" >O</button>
                        </div>
                        <div class="cell small-4">
                            <h2><strong>O</strong>beyed</h2>
                        </div>
                        <div class="cell small-7">
                            Obeying a skill is to practice it. Obeying a concept is to accept it as good and right.
                        </div>
                        <div class="cell small-1">
                            <button type="button" class="dt_multi_select empty-select-button select-button button" style="padding:5px" >S</button>
                        </div>
                        <div class="cell small-4">
                            <h2><strong>S</strong>hared</h2>
                        </div>
                        <div class="cell small-7">
                            Have you shared the concept or skill with others?
                        </div>
                        <div class="cell small-1">
                            <button type="button" class="dt_multi_select empty-select-button select-button button" style="padding:5px" >T</button>
                        </div>
                        <div class="cell small-4">
                            <h2><strong>T</strong>rained</h2>
                        </div>
                        <div class="cell small-7">
                            Have you trained someone else in the concept or skill?
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="reveal large" id="modal-large-cc" data-v-offset="0" data-reveal>
            <h3 id="modal-large-cc-title">&nbsp;<span class="show-for-small-only additional-close">Return to Checklist</span></h3>
            <hr>
            <div id="modal-large-cc-content"></div>
            <button class="close-button" data-close aria-label="Close modal" type="button">
               <span class="hide-for-small-only additional-close"> Return to Checklist</span><span aria-hidden="true"> &times; </span>
            </button>
        </div>
        <?php
    }

    /**
     * Register REST Endpoints
     * @link https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
     */
    public function add_endpoints() {
        $namespace = $this->root . '/v1';
        register_rest_route(
            $namespace, '/'.$this->type, [
                [
                    'methods'  => "POST",
                    'callback' => [ $this, 'update_record' ],
                    'permission_callback' => function( WP_REST_Request $request ){
                        $magic = new DT_Magic_URL( $this->root );
                        return $magic->verify_rest_endpoint_permissions_on_post( $request );
                    },
                ],
            ]
        );
    }

    public function update_record( WP_REST_Request $request ) {
        $params = $request->get_params();
        $params = dt_recursive_sanitize_array( $params );

        $post_id = $params["parts"]["post_id"]; //has been verified in verify_rest_endpoint_permissions_on_post()

        if ( isset( $params['field_key'] ) && !empty( $params['field_key'] ) && isset( $params['option_value'] ) && !empty( $params['option_value'] ) ){
            $fields = [
                $params['field_key'] => [
                    'values' => [
                        [
            'value' => $params['option_value'],
            'delete' => $params['turn_off']
                        ]
                    ]
                ],
            ];

            $update = DT_Posts::update_post( $this->post_type, $post_id, $fields, false, false );
            if ( is_wp_error( $update ) ){
                return $update;
            }

            if ( $params['turn_off'] ) {
                return 'off';
            } else {
                return 'on';
            }
        }

        return true;
    }

    /**
     * Post Type Tile Examples
     */
    public function dt_settings_apps_list( $apps_list ) {
        $apps_list[$this->meta_key] = [
            'key' => $this->meta_key,
            'url_base' => $this->root. '/'. $this->type,
            'label' => $this->page_title,
            'description' => $this->page_description,
        ];
        return $apps_list;
    }
}
Zume_Coaching_Checklist_Magic_Link::instance();
