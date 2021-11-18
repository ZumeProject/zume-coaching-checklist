<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Coaching_Checklist_Tile
{
    private static $_instance = null;
    public static function instance(){
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct(){
        add_filter( 'dt_details_additional_tiles', [ $this, "dt_details_additional_tiles" ], 10, 2 );
        add_filter( "dt_custom_fields_settings", [ $this, "dt_custom_fields" ], 10, 2 );
        add_action( "dt_details_additional_section", [ $this, "dt_add_section" ], 30, 2 );
    }

    /**
     * This function registers a new tile to a specific post type
     *
     * @param $tiles
     * @param string $post_type
     * @return mixed
     */
    public function dt_details_additional_tiles( $tiles, $post_type = "" ) {
        if ( $post_type === "contacts" ){
            $tiles["coaching_checklist"] = [ "label" => __( "Coaching Checklist", 'disciple-tools-coaching-checklist' ) ];
        }
        return $tiles;
    }

    /**
     * @param array $fields
     * @param string $post_type
     * @return array
     */
    public function dt_custom_fields( array $fields, string $post_type = "" ) {
        if ( $post_type === "contacts" ){
            $options = [
                "h" => [ "label" => _x( "H", "Coaching Checklist Initial for: Heard", 'disciple-tools-coaching-checklist' ) ],
                "o" => [ "label" => _x( "O", "Coaching Checklist Initial for: Obeyed", 'disciple-tools-coaching-checklist' ) ],
                "s" => [ "label" => _x( "S", "Coaching Checklist Initial for: Shared", 'disciple-tools-coaching-checklist' ) ],
                "t" => [ "label" => _x( "T", "Coaching Checklist Initial for: Trained", 'disciple-tools-coaching-checklist' ) ],
            ];

            $coaching_checklist_items = [

                /* session 1 */
//                1 => [
//                    'label' => _x( "God Uses Ordinary People", "coaching checklist", 'disciple-tools-coaching-checklist' ),
//                    'description' => "You'll see how God uses ordinary people doing simple things to make a big impact.",
//                    'url' => 'https://zume.training/god-uses-ordinary-people/'
//                    ],
                1 => _x( "God Uses Ordinary People", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                2 => _x( "Definition of Disciple & Church", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                3 => _x( "Breathing: Hearing & Obeying", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                4 => _x( "SOAPS Bible Reading", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                5 => _x( "Accountability Groups", "coaching checklist", 'disciple-tools-coaching-checklist' ),

                /* session 2 */
                6 => _x( "Consumer vs Producer Lifestyle", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                7 => _x( "Prayer Wheel (Hour in Prayer)", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                8 => _x( "Relational Stewardship (List 100)", "coaching checklist", 'disciple-tools-coaching-checklist' ),

                /* session 3 */
                9 => _x( "Kingdom Economy", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                10 => _x( "How to Share the Gospel", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                11 => _x( "How to Baptize", "coaching checklist", 'disciple-tools-coaching-checklist' ),

                /* session 4 */
                12 => _x( "3 Minute Testimony", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                13 => _x( "Greatest Blessing", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                14 => _x( "Duckling Discipleship", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                15 => _x( "Eyes to See Where the Kingdom Isn't", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                16 => _x( "Lord's Supper", "coaching checklist", 'disciple-tools-coaching-checklist' ),

                /* session 5 */
                17 => _x( "Prayer Walking", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                18 => _x( "Person of Peace", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                19 => _x( "BLESS Prayer Pattern", "coaching checklist", 'disciple-tools-coaching-checklist' ),

                /* session 6 */
                20 => _x( "Faithfulness is Better", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                21 => _x( "3/3 Group Format", "coaching checklist", 'disciple-tools-coaching-checklist' ),

                /* session 7 */
                22 => _x( "Training Cycle", "coaching checklist", 'disciple-tools-coaching-checklist' ),

                /* session 8 */
                23 => _x( "Leadership Cells", "coaching checklist", 'disciple-tools-coaching-checklist' ),

                /* session 9 */
                24 => _x( "Non-Sequential Growth", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                25 => _x( "Pace Matters", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                26 => _x( "Being Part of Two Churches", "coaching checklist", 'disciple-tools-coaching-checklist' ),

                /* session 10 */
                27 => _x( "Coaching Checklist", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                28 => _x( "Leadership in Networks", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                29 => _x( "Peer Mentoring Group", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                30 => _x( "Four Fields Tool", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                31 => _x( "Generational Mapping", "coaching checklist", 'disciple-tools-coaching-checklist' ),

            ];
            foreach ( $coaching_checklist_items as $item_key => $item_label ){
                $fields["coaching_checklist_" . $item_key ] = [
                    "name" => $item_label,
                    "default" => $options,
                    "tile" => "coaching_checklist",
                    "type" => "multi_select",
                    "hidden" => true,
                    "custom_display" => true,

                ];
            }
        }
        return $fields;
    }

    public function dt_add_section( $section, $post_type ) {
        if ( $section === "coaching_checklist" && $post_type === "contacts" ) {
            $post_fields = DT_Posts::get_post_field_settings( $post_type );
            $post = DT_Posts::get_post( $post_type, get_the_ID() );

            $total_done = 0;
            $total = 0;
            foreach ($post_fields as $field_key => $field_options ) {
                if ( isset( $field_options["tile"] ) && $field_options["tile"] === "coaching_checklist" ) {
                    $total += sizeof( $field_options["default"] );
                    if ( isset( $post[$field_key] ) ){
                        $total_done += sizeof( $post[$field_key] );
                    }
                }
            }
            ?>
            <p><?php esc_html_e( 'Completed', 'disciple-tools-coaching-checklist' ); ?> <?php echo esc_html( $total_done ); ?>/<?php echo esc_html( $total ); ?></p>
            <?php

            foreach ($post_fields as $field_key => $field_options ) :
                if ( isset( $field_options["tile"] ) && $field_options["tile"] === "coaching_checklist" ) :
                    $post_fields[$field_key]["hidden"] = false;
                    $post_fields[$field_key]["custom_display"] = false;

                    ?>
                    <div style="display: flex">
                        <div style="flex-grow: 1; overflow: hidden; white-space: nowrap; text-overflow: ellipsis">
                            <?php echo esc_html( $field_options["name"] ); ?>
                        </div>
                        <div style="">
                            <div class="small button-group" style="display: inline-block; margin-bottom: 5px">
                                <?php foreach ( $post_fields[$field_key]["default"] as $option_key => $option_value ): ?>
                                    <?php
                                    $class = ( in_array( $option_key, $post[$field_key] ?? [] ) ) ?
                                        "selected-select-button" : "empty-select-button"; ?>
                                    <button id="<?php echo esc_html( $option_key ) ?>" type="button" data-field-key="<?php echo esc_html( $field_key ); ?>"
                                            class="dt_multi_select <?php echo esc_html( $class ) ?> select-button button " style="padding:5px">
                                        <?php echo esc_html( $post_fields[$field_key]["default"][$option_key]["label"] ) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif;
            endforeach; ?>
        <?php }
    }
}
DT_Coaching_Checklist_Tile::instance();
