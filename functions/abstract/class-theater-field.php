<?php

/**
 * Theater_Field class.
 *
 * @since	0.16
 * @package	Theater/Abstracts
 */
abstract class Theater_Field {
	
	protected $name;
	protected $filters = array();
	protected $item;
	
	function __construct( $name, $filters = array(), $item ) {
		$this->name = $name;
		$this->filters = $filters;
		$this->item = $item;
	}
	
	function __invoke( $args = array() ) {
		return $this->get();
	}
	
	function __toString() {
		return $this->get_html();
	}
	
	protected function apply_template_filters( $value, $filters ) {
		foreach ( $filters as $filter ) {
			$value = $filter->apply_to( $value, $this->item );
		}
		return $value;
	}

	function get() {
		if ( $callback = $this->get_callback('get') ) {
			$value = call_user_func( $callback );
		} else if (method_exists($this->item, 'get_'.$this->name)) {
			$value = $this->item->{'get_'.$this->name}();			
		} else {
			$value = get_post_meta($this->item->ID, $this->name, true);
		}
		
		$value = apply_filters( 
			'theater/'.$this->item->get_name().'/field', 
			$value, $this->name, $this->item 
		);
		
		$value = apply_filters( 
			'theater/'.$this->item->get_name().'/field/'.$this->name, 
			$value, $this->item 
		);

		return $value;
	}
	
	protected function get_callback( $action ) {
		
		foreach( $this->item->get_fields() as $field) {

			if ( $field['id'] != $this->name ) {
				continue;
			}
			
			if ( !empty($field['callbacks'][$action]))	{
				return $field['callbacks'][$action];
			}
		};
		
		return false;
			
	}
	
	function get_html() {
		if ( $callback = $this->get_callback('get_html') ) {
			$html = call_user_func( $callback, $this->filters );
		} else if (method_exists($this->item, 'get_'.$this->name.'_html')) {
			$html = $this->item->{'get_'.$this->name.'_html'}( $this->filters );			
		} else {
			$value = $this->get();
			
			ob_start();
			?><div class="<?php echo $this->item->get_post_type(); ?>_<?php echo $this->name; ?>"><?php 
				echo $this->apply_template_filters( $value, $this->filters ); 
			?></div><?php
			$html = ob_get_clean();
		}
		
		$html = apply_filters( 
			'theater/'.$this->item->get_name().'/field/html', 
			$html, $this->name, $this->item 
		);
		
		$html = apply_filters( 
			'theater/'.$this->item->get_name().'/field/'.$this->name.'/html', 
			$html, $this->item 
		);
		
		return $html;

	}
	
}