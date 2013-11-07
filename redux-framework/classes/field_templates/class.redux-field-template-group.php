<?php

/**
 * @package Redux Framework
 * @version 4.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Don't duplicate me!
if( !class_exists( 'Redux_Field_Group' ) ) {
    class Redux_Field_Template_Group extends Redux_Field_Template{
        
        public static $version = '1.0.0';
        
        public static $_properties = array(
            'args' => array(
                'group_title' => '',
                'group_description' => '',
                'width' => 'full',
            ),
            'fields' => array(),
        );
        
        
        public function __construct( $field ){
            parent::__construct( $field );
            
            foreach( (array) $this->field['fields'] as $index => $field ){
                $this->field['fields'][$index] = Redux_Framework::parse_args(
                    $this->field['fields'][$index],
                    Redux_Field::$_properties
                );
                $this->field['fields'][$index]['name'] = $this->field['name'];
                //$this->field['fields'][$index]['value'] = $this->field['value'][$field['id']];
                $this->field['fields'][$index]['object'] = new Redux_Field($this->field['fields'][$index]);
            }
            
        }
        
        public function get_default_value(){
            
            if( !empty( $this->field['default'] ) ){
                //this check allows "groups" to define multiple default values to a field
                $values = $this->field['default'];
            }elseif( isset( $this->field['multi'] ) && $this->field['multi'] === true ){
                $values = array();
                foreach( (array) $this->field['fields'] as $index => $field ){
                    $values[0][$field['id']] = $field['object']->get_default_value();   
                }
            }else{
                $values = array();
                foreach( (array) $this->field['fields'] as $index => $field ){
                    $values[$field['id']] = $field['object']->get_default_value();   
                }
            }
            return $values;  
        }
        
        public function sanitize_value( $value ){
            if( isset( $this->field['multi'] ) && $this->field['multi'] === true ){
                $value = array_filter( $value );
                foreach( $value as $key => $val ){
                    foreach( (array) $this->field['fields'] as $index => $field ){
                        $value[$key][$field['id']] = $field['object']->sanitize_value( $value[$key][$field['id']] );   
                    }   
                }
            }else{
                foreach( (array) $this->field['fields'] as $index => $field ){
                    $value[$field['id']] = $field['object']->sanitize_value( $value[$field['id']] );   
                }
            }
            return $value;   
        }
        
        
        public function render( $name, $value ){
            
            if( $this->field['args']['group_title'] != '' ){
                echo '<h4 class="redux-group-title">' . $this->field['args']['group_title'] . '</h4>';
            }else{
                echo '<h4 class="redux-group-title">' . $this->field['title'] . '</h4>';
            }
            
            echo '<div class="redux-group-fields">';
            
            if( $this->field['description'] != '' ){
                echo '<div class="redux-group-description"><span class="redux-group-description description">' . $this->field['description'] . '</span></div>';
            }
            
            
            foreach( (array) $this->field['fields'] as $index => $field ){
                
                if( $field['type'] != 'group' ){
                    $title = ( isset( $field['sub_title'] ) ) ? '<h5 class="redux-group-field-title">' . $field['title'] . '</h5>' . '<span class="redux-row-description description">' . $field['sub_title'] . '</span>' : '<h5 class="redux-group-field-title">' . $field['title'] . '</h5>';
                                    
                    //echo $title;
                }else{
                    $title = '';
                }
                
                if( isset( $field['multi'] ) && $field['multi'] === true ){
                    $val = ( isset( $value[$field['id']] ) ) ? $value[$field['id']] : array();
                    $add_text = ( isset( $field['args']['add_text'] ) && $field['args']['add_text'] != '' ) ? $field['args']['add_text'] : __( 'Add Field', 'redux-framework');
                    
                    $sortable = ( $field['sortable'] === true ) ? ' redux-multi-field-sortable' : '';
                    
                    echo '<div class="redux-field redux-field-' . $field['type'] . ' redux-field-width-' . $field['args']['width'] . '" id="redux-field-' . $field['id'] . '">';
                    
                        echo $title;
                    
                        echo '<div class="redux-multi-field' . $sortable . '" id="redux-multi-field-' . $this->field['id'] . '" data-field-id="' . $field['id'] . '" data-name="' . $name . '[' . $field['id'] . ']" data-sortable-pattern="' . $name . '[' . $field['id'] . ']' . '[##sortable-index##]' . '">';
                    
                    
                            foreach( (array) $val as $_index => $_value ){
                                echo '<div class="redux-multi-instance" id="redux-field-' . $field['id'] . '-index-' . $_index . '" data-name="' . $name . '[' . $field['id'] . ']' . '[' . $_index . ']">';
                                
                                    $field['object']->template->render( $name . '[' . $field['id'] . ']' . '[' . $_index . ']', $_value );
                                echo '</div>'; 
                            }
                    
                            if( $field['args']['multi_show_empty'] == true ){
                                $count = ( count( $val ) == 0 ) ? 0 : count( $val );
                        
                                echo '<div class="redux-multi-instance" id="redux-field-' . $field['id'] . '-index-' . $count . '" data-name="' . $name . '[' . $field['id'] . '][' . $count . ']">';
                                    $field['object']->template->render( $name . '[' . $field['id'] . ']' . '[' . $count . ']', '' );
                                echo '</div>';
                            }
                    
                            echo '<div class="redux-multi-instance-clone" id="redux-field-' . $field['id'] . '-index-##' . $field['id'] . '-index##" data-name="' . $name . '[' . $field['id'] . '][##' . $field['id'] . '-index##]">';
                                $field['object']->template->render( $name . '[' . $field['id'] . ']' . '[##' . $field['id'] . '-index##]', '' );
                            echo '</div>';

                            echo '<a href="javascript:void(0);" class="redux-multi-field-clone button-' . $field['args']['add_class'] . '" title="' . $add_text . '" data-index-pattern="##' . $field['id'] . '-index##">' . $add_text . '</a>';
                        
                        echo '</div>';
                        $field['object']->description();
                        echo '<div class="clearfix"></div>';
                    echo '</div>';
                    
                }else{
                    
                    $val = ( isset( $value[$field['id']] ) ) ? $value[$field['id']] : '';
                    echo '<div class="redux-field redux-field-' . $field['type'] . ' redux-field-width-' . $field['args']['width'] . '" id="redux-field-' . $field['id'] . '">';
                        echo $title;
                        $field['object']->template->render( $name . '[' . $field['id'] . ']', $val );
                        $field['object']->description();
                        echo '<div class="clearfix"></div>';
                    echo '</div>';
                    
                }
            }
                echo '<div class="clearfix"></div>';
            echo '</div>';
        }
        
        public function description(){
         //empty as we dont want to duplicate it!   
        }

        
    }
}